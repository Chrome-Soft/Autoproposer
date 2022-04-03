<?php

namespace App;

use App\Jobs\Segmentify;
use App\Jobs\SegmentifyChunk;
use App\Jobs\Unsegmentify;
use App\Services\Segment\ExpressionNormalizer\NormalizerFactory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Tests\Feature\SegmentEqualityTest;

class Segment extends Model
{
    use HasSlug, SoftDeletes, Listable;

    const DEFAULT_SEGMENT = 'egyeb';

    protected $with = ['groups', 'products'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Segment $segment) {
            $segment->sequence = Segment::nextSequence();
        });
    }

    public function groups()
    {
        return $this->hasMany(SegmentGroup::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'segment_products')
            ->withPivot(['priority_id', 'id'])
            ->using(SegmentProduct::class);
    }

    public function segment_products()
    {
        return $this->hasMany(SegmentProduct::class);
    }

    public function recommendations()
    {
        return $this->hasMany(Recommendation::class);
    }

    public function appearance_template()
    {
        return $this->belongsTo(SegmentAppearanceTemplate::class, 'template_id');
    }

    public function syncGroups(array $groups)
    {
        $this->groups()->delete();

        foreach ($groups as $groupData) {
            $group = new SegmentGroup;
            $group->bool_type = Arr::get($groupData, 'bool_type');
            $group->segment_id = $this->id;
            $group->save();

            $group->addCriterias($groupData['criterias']);
        }
    }

    public function getUserData(array $paging, array $filters)
    {
        $query = DB::table('user_data')->where('segment_id', $this->id);

        if ($this->is_default) {
            $query->orWhere('segment_id', null);
        }

        foreach ($filters as $filter) {
            $relation = Relation::getFromCache($filter['relation']);
            $normalizer = NormalizerFactory::create($filter['column'], $relation->symbol, Arr::get($filter, 'value'));
            $normalizer->setQuery($query);
            $normalizer->normalize();
        }

        $count = $query->count();

        if (!empty($paging)) {
            $offset = ($paging['currentPage'] - 1) * $paging['itemsPerPage'];
            $query
                ->limit($paging['itemsPerPage'])
                ->offset($offset);
        }

        $items = $query->get();

        return compact('count', 'items');
    }

    public static function getUserDataStatistics()
    {
        $segments = Segment::all();
        foreach ($segments as $segment) {
            $userData = $segment->getUserData(['currentPage' => 1, 'itemsPerPage' => 10], []);
            $segment['user_data_count'] = $userData['count'];
            $segment['page_load_count'] = $segment->getPageLoadCount();
        }

        return $segments->all();
    }

    public function getPageLoadCount()
    {
        return DB::table('page_loads')
            ->leftJoin('user_data', 'page_loads.cookie_id', '=', 'user_data.cookie_id')
            ->where('user_data.segment_id', $this->id)
            ->count();
    }

    public function getInteractions()
    {
        $cookieIds = UserData::select('cookie_id')->where('segment_id', $this->id)->get()->pluck('cookie_id');
        $interactions = Interaction::whereIn('cookie_id', $cookieIds)->get();

        return $interactions->map(function ($x) {
            return [
                'type'          => $x->type,
                'product_ids'   => $x->items->pluck('item_id')->toArray()
            ];
        });
    }

    /**
     * Csak async job -ból lehet használni (Jobs/Segmentify)
     */
    public function segmentify(array $userDataIds)
    {
        DB::table('user_data')->whereIn('id', $userDataIds)->update(['segment_id' => $this->id]);
    }

    /**
     * Csak async job -ból lehet használni (Jobs/Unsegmentify)
     */
    public function unsegmentify()
    {
        DB::table('user_data')->where('segment_id', $this->id)->update(['segment_id' => null]);
    }

    public function replicate(array $except = null)
    {
        $model = parent::replicate($except); // TODO: Change the autogenerated stub
        $model->name = $this->name . ' Másolat';
        $model->push();

        $model->replicateGroups();

        return $model;
    }

    public function buildQuery($id = null): Builder
    {
        $query = DB::table('user_data')->select('user_data.id', 'user_data.segment_id');

        $foreignTables = $this->getForeignTables();
        foreach ($foreignTables as $table) {
            $query->leftJoin($table, $table . '.cookie_id', '=', 'user_data.cookie_id');
            $query->whereNotNull($table . '.id');
        }

        foreach ($this->groups as $i => $group) {
            $function = $this->getWhereFunction($this->groups, $i - 1);
            $group->buildQuery($query, $function);
        }

        if (!empty($foreignTables)) {
            $query->groupBy('user_data.id');
        }

        if ($id) {
            $query->where('id', $id);
        }

        return $query;
    }

    protected function getForeignTables()
    {
        $foreignSlugs = ['visited_url' => 'page_loads', 'visited_path' => 'page_loads', 'search_term' => 'search_terms'];
        $tables = [];

        foreach ($this->groups as $group) {
            foreach ($group->segment_group_criterias as $criteria) {
                if (in_array($criteria->criteria->slug, array_keys($foreignSlugs))) {
                    $tables[] = $foreignSlugs[$criteria->criteria->slug];
                }
            }
        }

        return array_unique($tables);
    }

    public function getWhereFunction(Collection $items, $index)
    {
        return isset($items[$index]) && $items[$index]->bool_type == 'or'
            ? 'orWhere'
            : 'where';
    }

    public function sameAs(Segment $segment)
    {
        if ($this->is_default) {
            return false;
        }

        foreach ($this->groups as $group) {
            foreach ($segment->groups as $otherGroup) {
                if (!$group->sameAs($otherGroup)) {
                    return false;
                }
            }
        }

        return true;
    }

    public function hasSame()
    {
        $segments = Segment::where('id', '!=', $this->id)->where('is_default', 0)->get();

        return $segments->contains(function ($x) { return $this->sameAs($x); });
    }

    public function getProductsByType(): array
    {
        $segmentProducts = $this->segment_products()->with('product')->with('priority')->get();
        $products = [
            'always'    => $segmentProducts->where('priority_id', '=', SegmentProductPriority::ALWAYS_PRESENT),
            'optional'  => $segmentProducts->where('priority_id', '=', SegmentProductPriority::OPTIONAL_PRESENT)
        ];

        $mapperFn = function (SegmentProduct $segmentProduct) {
            $x = $segmentProduct->product->load('photos');
            $x->priority = $segmentProduct->priority->slug;

            return $x;
        };

        $products['always'] = $products['always']->map($mapperFn);
        $products['optional'] = $products['optional']->map($mapperFn);

        foreach ($products as $type => $items) {
            $products[$type] = $products[$type]->map(function ($x) {
                $x->type_key = ProposerItemType::TYPE_PRODUCT;
                return $x;
            });
        }

        return $products;
    }

    public static function nextSequence()
    {
        $segment = Segment::orderByDesc('sequence')->first();
        return $segment ? $segment->sequence + 1 : 0;
    }

    public function getCriterias()
    {
        $groups = $this->groups->load('criterias');
        $criterias = new Collection;

        foreach ($groups as $group) {
            $criterias = $criterias->concat($group->criterias);
        }

        return $criterias;
    }

    protected function replicateGroups()
    {
        foreach ($this->groups as $group) {
            $newGroup = $group->replicate();
            $newGroup->id = null;
            $newGroup->segment_id = $this->id;

            $newGroup->save();

            $newGroup->replicateCriterias($group);
        }
    }

    protected function excludedColumns()
    {
        return ['is_default', 'deleted_at', 'updated_at', 'user_id', 'sequence', 'template_id'];
    }

    protected function excludedFromFilters()
    {
        return ['user_id'];
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\Feature\PartnerApiKeyLifecycleTest;

class Partner extends Model
{
    use SoftDeletes, HasSlug, HasUser;
    use Listable {
        excludedColumns as protected parentExcludedColumns;
    }
    use Viewable {
        excludedViewFields as protected parentExcludedViewField;
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function (Partner $partner) {
            $partner->createApiKey();
            $partner->createHomePage();
        });

        static::updating(function (Partner $partner) {
            $partner->apiKey()->update([
                'name' => $partner->slug
            ]);
        });

        static::deleting(function (Partner $partner) {
            $partner->apiKey()->delete();
            $partner->apiKey()->withTrashed()->update(['partner_id' => null]);
        });

        static::restoring(function (Partner $partner) {
            $partner->createApiKey();
        });
    }

    public function proposers()
    {
        return $this->hasMany(Proposer::class);
    }

    public function apiKey()
    {
        return $this->hasOne(ApiKey::class);
    }

    public function pages()
    {
        return $this->hasMany(Page::class);
    }

    public function createApiKey()
    {
        $this->apiKey()->create([
            'name'  => $this->slug,
            'key'   => ApiKey::generate()
        ]);
    }

    public function createHomePage()
    {
        $this->pages()->create([
            'name'      => 'Főoldal',
            'url'       => '/'
        ]);
    }

    public function getTrackableUrl(string $url = null)
    {
        if (!$this->is_anonymus_domain) {
            return $url;
        }

        $components = parse_url($url);
        if (!$components) return null;

        return Arr::get($components, 'path', null);
    }

    public static function getUserDataStatistics()
    {
        $partners = Partner::all();

        foreach ($partners as $partner) {
            $partner['user_data_count'] = number_format(UserData::where('partner_external_id', $partner->external_id)->count(), 0, '.', ' ');
            $partner['page_load_count'] = number_format(PageLoad::where('partner_external_id', $partner->external_id)->count(), 0, '.', ' ');
        }

        return $partners->all();
    }

    protected function excludedColumns()
    {
        return array_merge(
            ['external_id'], $this->parentExcludedColumns()
        );
    }

    protected function excludedViewFields()
    {
        return array_merge(
            ['external_id'], $this->parentExcludedViewField()
        );
    }

    protected function customCasters()
    {
        return [
            'is_anonymus_domain'    => function ($value) { return $this->boolCaster($value); }
        ];
    }
}

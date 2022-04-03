<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace App;

use App\Services\Segment\ExpressionNormalizer\NormalizerFactory;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

trait Listable
{
    public function getListData($paging, $filters)
    {
        $listItems = $this->getListItems($paging, $filters ?? []);
        return [
            'columns'               => $this->getListColumns(),
            'items'                 => $listItems['items'],
            'count'                 => $listItems['count'],
            'hiddenColumns'         => array_flip($this->hiddenColumns()),
            'actions'               => $this->actions(),
            'excludedFromFilters'   => $this->excludedFromFilters()
        ];
    }

    protected function getListColumns()
    {
        return collect($this->getTableColumns())
            ->filter(function ($x) { return !in_array($x, $this->excludedColumns()); })
            ->flip()
            ->map(function ($x, $key) { return __("list.{$key}"); })
            ->sortBy(function ($x, $key) {
                return Arr::get($this->columnOrders(), $key, 20);
            })->toArray();
    }

    protected function getListItems(array $paging, $filters = [])
    {
        $columns = $this->getQueryColumns();

        $query = DB::table($this->getTable())
            ->select($columns);

        $this->addJoins($query);
        $this->addFilters($query, $filters);
        $this->addOrderBy($query);

        $count = $query->count();

        $this->addLimit($query, $paging);

        return [
            'items' => $this->castItems($query->get()),
            'count' => $count
        ];
    }

    protected function excludedColumns()
    {
        return ['updated_at', 'deleted_at', 'user_id'];
    }

    protected function hiddenColumns()
    {
        return ['id', 'slug'];
    }

    protected function customCasters()
    {
        return [];
    }

    protected function boolCaster($value)
    {
        return $value ? 'Igen' : 'Nem';
    }

    protected function defaultOrderBy()
    {
        return [
            'column'    => 'name',
            'direction' => 'asc'
        ];
    }

    protected function customActions()
    {
        return [];
    }

    protected function relationMappers()
    {
        return [];
    }

    /**
     * Minél nagyobb értéket kap egy mező, annál később jelenik meg a listában.
     * Ha egy nem található meg itt, 10 default értéket kap
     * @return array
     */
    protected function columnOrders()
    {
        return [
            'name'          => 0,
            'description'   => 1,
            'created_at'    => 100
        ];
    }

    private function casters()
    {
        return array_merge(
            [
                'created_at' => function ($value) {
                    $date = new \Carbon\Carbon($value);

                    if ($date->diffInDays() <= 5)
                        return $date->diffForHumans();

                    return $date->format('Y-m-d H:i');
                },
                'description'   => function ($value) {
                    return $value ? substr($value, 0, 20) . '...' : '';
                }
            ],
            $this->customCasters()
        );
    }

    protected function actions()
    {
        return array_merge(
            [
                'view'  => [
                    'label' => 'Megtekintés',
                    'style' => 'secondary'
                ],
                'edit'  => [
                    'label' => 'Szerkesztés',
                    'style' => 'primary'
                ]
            ],
            $this->customActions()
        );
    }

    protected function excludedFromFilters()
    {
        return [];
    }

    protected function castItems($items)
    {
        $cast = function ($value, $caster) {
            switch (is_string($caster))
            {
                case true:
                    return "{$value}{$caster}";
                    break;
                case false:
                    return $caster($value);
                    break;
            }
        };

        $casters = $this->casters();

        foreach ($casters as $column => $caster) {
            foreach ($items as $item) {
                if (property_exists($item, $column)) {
                    $item->{$column} = $cast($item->{$column}, $caster);
                }
            }
        }


        return $items;
    }

    protected function excludedIds(): array
    {
        return [];
    }

    /**
     * Visszaadja az összes mezőt, 'table'.'column' formában, és a join által használt, külső tábla mezőket is
     * @return array
     */
    private function getQueryColumns()
    {
        $columns = array_keys($this->getListColumns());
        $columns = collect($columns)->map(function ($x) { return $this->fullColumnName($x); })->toArray();

        return array_merge($columns, $this->getJoinColumns());
    }

    private function addJoins($query)
    {
        foreach ($this->relationMappers() as $foreignKey => $relation)
            $query->join($relation['table'], $this->fullColumnName($foreignKey), '=', $relation['table'] . '.id');
    }

    private function addFilters($query, $filters = [])
    {
        foreach ($filters as $filter) {
            $relation = Relation::getFromCache($filter['relation']);
            $normalizer = NormalizerFactory::create($this->fullColumnName($filter['column']), $relation->symbol, Arr::get($filter, 'value'));
            $normalizer->setQuery($query);

            $normalizer->normalize();
        }

        $query->whereNotIn('id', $this->excludedIds());
    }

    private function addOrderBy($query)
    {
        $orderBy = $this->defaultOrderBy();
        $query->orderBy($this->fullColumnName($orderBy['column']), $orderBy['direction']);
    }

    private function addLimit($query, $paging)
    {
        $query
            ->limit($paging['itemsPerPage'])
            ->offset(($paging['currentPage'] - 1) * $paging['itemsPerPage']);
    }

    private function fullColumnName($column)
    {
        return "{$this->getTable()}.{$column}";
    }

    private function getJoinColumns()
    {
        $columns = collect($this->relationMappers())
            ->map(function ($x, $foreignKey) { return DB::raw("{$x['table']}.{$x['column']} as {$foreignKey}"); })
            ->toArray();

        return array_values($columns);
    }
}

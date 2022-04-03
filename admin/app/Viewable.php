<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace App;


use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

trait Viewable
{
    public function getViewData()
    {
        return [
            'fields'    => $this->getViewFields(),
            'data'      => $this->getViewValues()
        ];
    }

    protected function getViewFields(): array
    {
        $fields = collect($this->getTableColumns())
            ->filter(function ($x) { return !in_array($x, $this->excludedViewFields()); })
            ->sortBy(function ($x) {
                return Arr::get($this->columnOrders(), $x, 20);
            })
            ->toArray();

        return array_values($fields);
    }

    protected function getViewValues()
    {
        $fields = [];
        foreach ($this->getViewFields() as $field) {
            $fields[$field] = [
                'label' => __("list.{$field}"),
                'value' => $this->getValue($field)
            ];
        }

        return $fields;
    }

    protected function getValue($field)
    {
        $casters = $this->viewCasters();
        $relations = array_merge($this->viewRelationMappers(), $this->relationMappers());

        if (!isset($relations[$field])) {
            return $this->castValue($casters, $field, $this->{$field});
        }

        $relation = $relations[$field];
        $record = DB::table($relation['table'])->where('id', '=', $this->{$field})->first();
        return $record->{$relation['column']};
    }

    private function castValue($casters, $field, $value) {
        $caster = Arr::get($casters, $field, '');
        if (is_scalar($caster)) return "{$value}{$caster}";

        return $caster($value);
    }

    protected function excludedViewFields(): array
    {
        return ['id','updated_at','slug','deleted_at'];
    }

    protected function viewRelationMappers()
    {
        return [
            'user_id' => [
                'table'     => 'users',
                'column'    => 'name'
            ],
        ];
    }

    private function viewCasters()
    {
        return array_merge(
            [
                'created_at' => function ($value) {
                    $date = new \Carbon\Carbon($value);
                    return $date->format('Y-m-d H:i');
                }
            ],
            $this->customCasters()
        );
    }
}

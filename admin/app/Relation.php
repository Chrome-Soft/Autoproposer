<?php

namespace App;

class Relation extends Model
{
    use Cacheable;

    const EQUAL                 = 1;
    const NOT_EQUAL             = 2;
    const LESS_THEN_OR_EQUAL    = 3;
    const GREATER_THEN_OR_EQUAL = 4;
    const CONTAIN               = 5;
    const NOT_CONTAIN           = 6;
    const EMPTY                 = 7;
    const NOT_EMPTY             = 8;

    public function addWhere($query, $column, $value = null)
    {
        switch ($this->id) {
            case static::EQUAL:
            case static::NOT_EQUAL:
            case static::LESS_THEN_OR_EQUAL:
            case static::GREATER_THEN_OR_EQUAL:
                $query->where($column, $this->symbol, $value);
                break;

            case static::CONTAIN:
            case static::NOT_CONTAIN:
                $query->where($column, $this->symbol, "%{$value}%");
                break;

            case static::EMPTY:
                $query->whereNull($column);
                break;
            case static::NOT_EMPTY:
                $query->whereNotNull($column);
                break;
        }
    }
}

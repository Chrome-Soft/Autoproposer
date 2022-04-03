<?php

namespace App;

use Illuminate\Support\Facades\Cache;

trait Cacheable
{
    public static function getAllFromCache()
    {
        return Cache::get(with(new static)->getTable());
    }

    public static function getFromCache($id)
    {
        return static::getAllFromCache()->first(function ($x) use ($id) {
            return $x->id == $id;
        });
    }

    public static function getMoreFromCache($ids)
    {
        return static::getAllFromCache()->whereIn('id', $ids);
    }
}
<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace App;


use Illuminate\Support\Str;

trait HasSlug
{
    protected static function slugField()
    {
        return 'slug';
    }

    protected static function nameField()
    {
        return 'name';
    }

    public function getRouteKeyName()
    {
        return static::slugField();
    }

    protected static function bootHasSlug()
    {
        static::creating(function (Model $model) {
            $model->{static::slugField()} = Str::slug($model->{static::nameField()});
        });

        static::updating(function (Model $model) {
            $model->{static::slugField()} = Str::slug($model->{static::nameField()});
        });
    }
}

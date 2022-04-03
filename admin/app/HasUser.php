<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace App;

trait HasUser
{
    protected static function userIdField()
    {
        return 'user_id';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function bootHasUser()
    {
        static::creating(function (Model $model) {
            // Tesztek miatt kell. Ott nincs mindig bejelentkezett user, hanem model factory -val csinálunk usert, és ilyenkor felül írná null -ra
            if ($model->{static::userIdField()} == null) {
                $model->{static::userIdField()} = auth()->id();
            }
        });

        static::updating(function (Model $model) {
            $model->{static::userIdField()} = auth()->id();
        });
    }
}

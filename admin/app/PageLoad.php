<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PageLoad extends Model
{
    protected $guarded = [];

    public static function updateUserId($userId, $cookieId)
    {
        DB::table('page_loads')
            ->where('cookie_id', $cookieId)
            ->update(['user_id' => $userId]);
    }
}

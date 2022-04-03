<?php

namespace App;

class Language extends Model
{
    public function currency()
    {
        return $this->hasOne(Currency::class);
    }
}

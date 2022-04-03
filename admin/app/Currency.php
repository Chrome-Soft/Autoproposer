<?php

namespace App;

class Currency extends Model
{
    public function language()
    {
        return $this->belongsTo(Language::class);
    }
}

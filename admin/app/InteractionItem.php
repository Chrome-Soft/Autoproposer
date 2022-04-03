<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InteractionItem extends Model
{
    protected $guarded = ['id'];

    public function item()
    {
        return $this->morphTo();
    }

    public function interaction()
    {
        return $this->belongsTo(Interaction::class);
    }
}

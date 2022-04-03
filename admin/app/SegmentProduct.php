<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class SegmentProduct extends Pivot
{
    protected $primaryKey = 'id';
    public $incrementing = true;

    protected $table = 'segment_products';

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function segment()
    {
        return $this->belongsTo(Segment::class);
    }

    public function priority()
    {
        return $this->belongsTo(SegmentProductPriority::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

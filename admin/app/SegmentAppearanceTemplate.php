<?php

namespace App;

class SegmentAppearanceTemplate extends Model
{
    use HasSlug, Cacheable;

    public function segment()
    {
        return $this->hasOne(Segment::class, 'template_id');
    }
}

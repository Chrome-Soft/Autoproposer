<?php

namespace App;

use Illuminate\Support\Str;

class ProductPhoto extends PhotoPivot
{
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getPublicPathAttribute()
    {
        return Str::startsWith($this->getImagePath(), 'http')
            ? $this->image_path
            : parent::getPublicPathAttribute();
    }

    public function getPublicUrlAttribute()
    {
        return Str::startsWith($this->getImagePath(), 'http')
            ? $this->image_path
            : parent::getPublicUrlAttribute();
    }
}

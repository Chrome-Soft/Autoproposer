<?php

namespace App;

class ProductAttributeType extends Model
{
    const TYPE_TEXT = 1;
    const TYPE_NUMBER = 2;
    const TYPE_BOOL = 3;
    const TYPE_DATE = 4;
    const TYPE_DATE_INTERVAL = 5;

    protected $casts = [
        'properties'    => 'array'
    ];

    public function attributes()
    {
        return $this->hasMany(ProductAttribute::class);
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductProductAttribute extends Pivot
{
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    public function product_attribute()
    {
        return $this->belongsTo(ProductAttribute::class);
    }
    
    public function getValueAttribute($value)
    {
        if ($this->product_attribute->type_id == ProductAttributeType::TYPE_BOOL) {
            return $value == 'on' ? 'Igen' : 'Nem';
        }

        if ($this->product_attribute->type->properties['numberOfElems'] == 2) {
            return json_decode($value, true);
        }

        return $value;
    }
}

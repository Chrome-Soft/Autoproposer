<?php

namespace App;

use Illuminate\Support\Str;

class ProductAttribute extends Model
{
    use HasSlug, Listable, Viewable;

    const DISCOUNT_SLUG  = 'kartya-kedvezmeny';

    protected $with = ['type'];
    
    public function type()
    {
        return $this->belongsTo(ProductAttributeType::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_product_attribute')
            ->withPivot('value')
            ->using(ProductProductAttribute::class);
    }

    public function path($uri = ''): string
    {
        $url = "/product-attributes/{$this->slug}";
        return $uri ? "{$url}/{$uri}" : $url;
    }

    public static function getExtraAttributesBy(array $ids = [])
    {
        $attrs = ProductAttribute::whereIn('id', $ids)->get();
        return $attrs->where('type.properties.numberOfElems', '=', 2);
    }

    public static function createImported(string $name)
    {
        return ProductAttribute::create([
            'name'          => $name,
            'slug'          => Str::slug($name),
            'type_id'       => ProductAttributeType::where('id', ProductAttributeType::TYPE_TEXT)->first()->id,
            'is_imported'   => true
        ]);
    }

    protected function customCasters()
    {
        return [
            'is_imported' => function ($value) { return $this->boolCaster($value); }
        ];
    }

    protected function relationMappers()
    {
        return [
            'type_id' => [
                'table'     => 'product_attribute_types',
                'column'    => 'name'
            ]
        ];
    }

    protected function viewRelationMappers()
    {
        return [];
    }

    protected function excludedFromFilters()
    {
        return ['type_id'];
    }
}

<?php

namespace App;

class Recommendation extends Model
{
    protected $with = ['product'];


    public function segment()
    {
        return $this->belongsTo(Segment::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public static function getBySegment(Segment $segment, $excludedIds, $limit)
    {
        $productType = ProposerItemType::getAllFromCache()->where('key', ProposerItemType::TYPE_PRODUCT)->first();

        return static::with('product.photos')
            ->where('segment_id', $segment->id)
            ->whereNotIn('product_id', $excludedIds)
            ->orderBy('order')
            ->limit($limit)
            ->get()
            ->map(function ($x) use ($productType) {
                $product = $x->product;
                $product->type_key = $productType->key;
                return $product;
            });
    }
}

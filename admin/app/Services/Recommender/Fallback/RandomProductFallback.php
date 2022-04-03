<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace App\Services\Recommender\Fallback;


use App\Product;
use App\Segment;
use Illuminate\Support\Collection;

class RandomProductFallback extends Fallback
{
    public function getProducts(Segment $segment, int $n, array $excludedProductIds): Collection
    {
        $products = Product::with('photos')
            ->whereNotIn('id', $excludedProductIds)
            ->limit($n)
            ->get();

        return $this->addTypeKey($products);
    }
}

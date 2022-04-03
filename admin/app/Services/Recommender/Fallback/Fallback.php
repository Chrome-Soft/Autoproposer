<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace App\Services\Recommender\Fallback;


use App\Product;
use App\Segment;
use Illuminate\Support\Collection;

abstract class Fallback
{
    abstract public function getProducts(Segment $segment, int $n, array $excludedProductIds): Collection;

    protected function addTypeKey(Collection $products): Collection
    {
        return $products->map(function ($x) {
            $x->type_key = 'product';
            return $x;
        });
    }
}

<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace App\Services\Recommender\Fallback;


use App\Segment;
use Illuminate\Support\Collection;

class FallbackComposition extends Fallback
{
    /**
     * @var array
     */
    private $children;

    /**
     * FallbackComposition constructor.
     * @param array $children   Fallback objects
     */
    public function __construct(array $children)
    {
        $this->children = $children;
    }

    public function getProducts(Segment $segment, int $n, array $excludedProductIds): Collection
    {
        $products = collect([]);
        foreach ($this->children as $child) {
            /** @var $child Fallback */
            $childProducts = $child->getProducts($segment, $n - $products->count(), $excludedProductIds);
            $products = $products->merge($childProducts);

            if ($products->count() >= $n)
                break;

            foreach ($products as $product)
                $excludedProductIds[] = $product->id;
        }

        return $products;
    }
}

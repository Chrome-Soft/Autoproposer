<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace App\Services\Recommender\Fallback;


class FallbackFactory
{
    public function createDefaultComposition() {
        return new FallbackComposition([
            new PopularProductFallback,
            new RandomProductFallback
        ]);
    }
}

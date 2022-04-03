<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace App\Services\Recommender\Fallback;


use App\Product;
use App\Segment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PopularProductFallback extends Fallback
{
    public function getProducts(Segment $segment, int $n, array $excludedProductIds): Collection
    {
        $ids = join(',', $excludedProductIds);

        $query = "
            select p.id
            from interactions i
            left join interaction_items ii on ii.interaction_id = i.id
            left join products p on p.id = ii.item_id
            where p.id is not null
            and p.id NOT IN ({$ids})
            group by ii.item_id
            order by sum(case
                when `type` = 'view' then 1
                when `type` = 'buy' then 2
                else 0
            end) desc
            limit {$n}";

        $ids = $this->getIds(DB::select($query));
        return $this->convertProducts($ids);
    }

    protected function getIds(array $queryResult): array
    {
        return collect($queryResult)
            ->pluck('id')
            ->all();
    }

    protected function convertProducts(array $ids): Collection
    {
        $products = Product::with('photos')
            ->whereIn('id', $ids)
            ->get();

        return $this->addTypeKey($products);
    }
}

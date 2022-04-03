<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace App\Services\Segment\ExpressionNormalizer;

use App\Page;
use Illuminate\Support\Facades\DB;

class VisitedUrlNormalizer extends PageVisitNormalizer
{
    protected function getPage(array $parsedValue)
    {
        return [
            'page' => Page::where('id', $parsedValue['page_id'])->first()
        ];
    }

    protected function getWhere(array $pages, $operator, $relation)
    {
        $this->query->{$this->whereFunction}(function ($q) use ($operator, $relation, $pages) {
            $q->where('page_loads.from_url', $relation, $pages['page']->url);

            if ($operator == 'OR') {
                $q->orWhere('page_loads.to_url', $relation, $pages['page']->url);
            } else {
                $q->where('page_loads.to_url', $relation, $pages['page']->url);
            }
        });


    }

    protected function getOperator(string $mappedRelation)
    {
        return $mappedRelation == 'LIKE' ? 'OR' : 'AND';
    }
}

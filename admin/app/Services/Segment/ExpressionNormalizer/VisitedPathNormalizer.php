<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace App\Services\Segment\ExpressionNormalizer;

use App\Page;
use Illuminate\Support\Facades\DB;

class VisitedPathNormalizer extends PageVisitNormalizer
{
    protected function getPage(array $parsedValue)
    {
        return [
            'from'  => Page::where('id', $parsedValue['from_page_id'])->first(),
            'to'    => Page::where('id', $parsedValue['to_page_id'])->first()
        ];
    }

    protected function getWhere(array $pages, $operator, $relation)
    {
        $this->query->{$this->whereFunction}(function ($q) use ($relation, $pages) {
            $q->where('page_loads.from_url', $relation, $pages['from']->url);
            $q->where('page_loads.to_url', $relation, $pages['to']->url);
        });
    }

    protected function getOperator(string $mappedRelation)
    {
        return 'AND';
    }
}

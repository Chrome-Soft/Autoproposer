<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace App\Services\Segment\ExpressionNormalizer;


use App\Relation;

abstract class PageVisitNormalizer extends ExpressionNormalizer
{
    abstract protected function getPage(array $parsedValue);
    abstract protected function getWhere(array $pages, $operator, $relation);
    abstract protected function getOperator(string $mappedRelation);

    public function normalize()
    {
        $parsedValue = $this->parseValue();
        $pages = $this->getPage($parsedValue);
        $mappedRelation = $this->mapRelation();
        $operator = $this->getOperator($mappedRelation);

        return $this->getWhere($pages, $operator, $mappedRelation);
    }

    protected function parseValue()
    {
        return json_decode($this->value, true);
    }

    protected function mapRelation()
    {
        $relation = Relation::where('symbol', $this->relation)->first();

        return ($relation->id == Relation::EQUAL)
            ? 'LIKE'
            : 'NOT LIKE';
    }
}

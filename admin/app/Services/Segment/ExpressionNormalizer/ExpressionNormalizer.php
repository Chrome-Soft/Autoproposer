<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace App\Services\Segment\ExpressionNormalizer;


abstract class ExpressionNormalizer
{
    protected $field;
    protected $relation;
    protected $value;
    protected $query;
    protected $whereFunction;

    public function __construct($field, $relation, $whereFunction, $value = null)
    {
        $this->field = $field;
        $this->relation = $relation;
        $this->value = $value;
        $this->whereFunction = $whereFunction;
    }

    public function setQuery($query)
    {
        $this->query = $query;
    }

    abstract public function normalize();
}

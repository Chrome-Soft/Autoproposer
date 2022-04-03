<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace App\Services\Segment\ExpressionNormalizer;


class NumericNormalizer extends ExpressionNormalizer
{
    public function normalize()
    {
        $this->query->{$this->whereFunction}($this->field, $this->relation, $this->value);
    }
}

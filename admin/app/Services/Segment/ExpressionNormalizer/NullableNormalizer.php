<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace App\Services\Segment\ExpressionNormalizer;


class NullableNormalizer extends ExpressionNormalizer
{
    public function normalize()
    {
        $this->relation == 'IS NULL'
            ? $this->query->{$this->whereFunction}($this->field, null)
            : $this->query->{$this->whereFunction}($this->field, '!=', null);
    }
}

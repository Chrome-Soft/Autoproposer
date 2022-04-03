<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace App\Services\Segment\ExpressionNormalizer;


use Illuminate\Support\Facades\DB;

class BoolNormalizer extends ExpressionNormalizer
{
    public function normalize()
    {
        switch (strtolower($this->value)) {
            case 'igen': $value = 1; break;
            case 'nem': $value = 0; break;
            default: $value = 1; break;
        }

        $this->query->{$this->whereFunction}($this->field, $this->relation, $value);
    }
}

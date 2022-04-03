<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace App\Services\Segment\ExpressionNormalizer;

use App\Page;
use Illuminate\Support\Facades\DB;

class SearchTermNormalizer extends ExpressionNormalizer
{
    public function normalize()
    {
        $this->query->{$this->whereFunction}(function ($q) {
            // TODO ezt meg lehetne oldani composition -vel valahogy, úgy hogy a ContainsNOrmalizer logikája legyen használva
            $value = ($this->relation == 'LIKE' || $this->relation == 'NOT LIKE') ? "%{$this->value}%" : $this->value;
            $q->where('search_terms.search_term', $this->relation, $value);
        });
    }
}

<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace App\Services\Segment\CriteriaRelations;


use App\Relation;
use Illuminate\Database\Eloquent\Collection;

class NumberRelations implements CriteriaRelations
{
    public function get(): Collection
    {
        return Relation::getMoreFromCache([Relation::EQUAL, Relation::NOT_EQUAL, Relation::LESS_THEN_OR_EQUAL,
            Relation::GREATER_THEN_OR_EQUAL, Relation::EMPTY, Relation::NOT_EMPTY]);
    }
}

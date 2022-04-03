<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace App\Services\Segment\CriteriaRelations;


use App\Relation;
use Illuminate\Database\Eloquent\Collection;

class AllRelations implements CriteriaRelations
{
    public function get(): Collection
    {
        return Relation::getAllFromCache();
    }
}

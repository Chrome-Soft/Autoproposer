<?php

namespace App\Services\Segment\CriteriaRelations;

use Illuminate\Database\Eloquent\Collection;

interface CriteriaRelations
{
    public function get(): Collection;
}
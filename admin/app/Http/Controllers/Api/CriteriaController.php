<?php

namespace App\Http\Controllers\Api;

use App\Criteria;

class CriteriaController extends Controller
{
    /**
     * Ha egy kritérium select típusú és tartoznak hozzá választható option -ök, ez a metódus adja vissza azokat
     * @param Criteria $criteria
     */
    public function options($id)
    {
        $criteria = Criteria::where('id', $id)->first();
        return $criteria->options->get();
    }

    public function availableRelations($id)
    {
        $criteria = Criteria::where('id', $id)->first();
        return $criteria->availableRelations;
    }
}

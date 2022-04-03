<?php

namespace App;

use Illuminate\Support\Arr;

class SegmentGroup extends Model
{
    use HasBoolType;

    protected $with = ['criterias'];

    public function segment()
    {
        return $this->belongsTo(Segment::class);
    }

    public function criterias()
    {
        return $this->belongsToMany(Criteria::class, 'segment_group_criterias')
            ->withPivot(['value', 'bool_type', 'relation_id'])
            ->using(SegmentGroupCriteria::class);
    }

    public function segment_group_criterias()
    {
        return $this->hasMany(SegmentGroupCriteria::class);
    }

    public function addCriterias(array $criterias)
    {
        foreach ($criterias as $i => $criteria) {
            $value = Arr::get($criteria, 'value', '');
            $normalizedValue = is_array($value)
                ? json_encode($value)
                : $value;

            $groupCriteria = new SegmentGroupCriteria;
            $groupCriteria->value = $normalizedValue;
            $groupCriteria->criteria_id = $criteria['criteria'];
            $groupCriteria->relation_id = $criteria['relation'];
            $groupCriteria->segment_group_id = $this->id;

            if ($i != count($criterias) - 1) {
                $groupCriteria->bool_type = Arr::get($criteria, 'bool_type');
            }

            $groupCriteria->save();
        }
    }

    public function replicateCriterias($oldGroup)
    {
        foreach ($oldGroup->segment_group_criterias as $criteria) {
            $newCriteria = $criteria->replicate();
            $newCriteria->id = null;
            $newCriteria->segment_group_id = $this->id;

            $newCriteria->save();
        }
    }

    public function buildQuery($query, $whereFunction)
    {
        $query->{$whereFunction}(function ($q) {
            foreach ($this->segment_group_criterias as $j => $criteria) {
                $criteria->buildQuery($q, $j);
            }
        });
    }

    public function sameAs(SegmentGroup $group)
    {
        if ($this->bool_type != $group->bool_type) {
            return false;
        }

        $equalities = [];
        foreach ($this->segment_group_criterias as $criteria) {
            $equalities[$criteria->id] = [];

            foreach ($group->segment_group_criterias as $otherCriteria) {
                $equalities[$criteria->id][$otherCriteria->id] = $criteria->sameAs($otherCriteria);
            }

            $equalities[$criteria->id] = collect($equalities[$criteria->id])->contains(true);
        }

        return !collect($equalities)->contains(false);
    }
}

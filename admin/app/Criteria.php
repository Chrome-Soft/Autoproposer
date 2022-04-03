<?php

namespace App;

use App\Services\Segment\CriteriaOptions\OptionsFactory;
use App\Services\Segment\CriteriaRelations\CriteriaRelations;
use App\Services\Segment\CriteriaRelations\CriteriaRelationsFactory;

class Criteria extends Model
{
    public $timestamps = false;

    use Cacheable;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public function getOptionsAttribute()
    {
        return OptionsFactory::create($this);
    }

    public function getRouteKeyName()
    {
        return 'id';
    }

    public function segment_group()
    {
        return $this->belongsToMany(SegmentGroup::class, 'segment_group_criterias')
            ->withPivot(['value', 'bool_type'])
            ->using(SegmentGroupCriteria::class);
    }

    public function getAvailableRelationsAttribute()
    {
        return CriteriaRelationsFactory::create($this->slug)->get();
    }

    public static function getAvailableRelationMap()
    {
        $criterias = Criteria::getAllFromCache();
        $map = [];

        foreach ($criterias as $criteria) {
            $map[$criteria->id] = array_values($criteria->availableRelations->toArray());
        }

        return $map;
    }
}

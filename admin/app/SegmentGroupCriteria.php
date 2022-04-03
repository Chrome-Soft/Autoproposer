<?php

namespace App;

use App\Services\Segment\ExpressionNormalizer\NormalizerFactory;
use App\Services\Segment\ValuePresenter\ValuePresenterFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class SegmentGroupCriteria extends Pivot
{
    use HasBoolType;

    protected $with = ['relation'];
    protected $table = 'segment_group_criterias';

    public function segment_group()
    {
        return $this->belongsTo(SegmentGroup::class, 'segment_group_id');
    }

    public function criteria()
    {
        return $this->belongsTo(Criteria::class);
    }

    public function relation()
    {
        return $this->belongsTo(Relation::class);
    }

    public function getNormalizedValueAttribute($value)
    {
        return (ValuePresenterFactory::create($this->criteria->slug, $this->value))->present();
    }

    public function buildQuery($query, $index)
    {
        $fn = $this->segment_group->segment->getWhereFunction($this->segment_group->segment_group_criterias, $index - 1);

        $normalizer = NormalizerFactory::create($this->criteria->slug, $this->relation->symbol, $this->value, $fn);
        $normalizer->setQuery($query);
        $normalizer->normalize();
    }

    public function sameAs(SegmentGroupCriteria $criteria)
    {
        if ($this->criteria_id != $criteria->criteria_id) {
            return false;
        }

        if ($this->relation_id != $criteria->relation_id) {
            return false;
        }

        if ($this->value != $criteria->value) {
            return false;
        }

        if ($this->bool_type != $criteria->bool_type) {
            return false;
        }

        return true;
    }
}

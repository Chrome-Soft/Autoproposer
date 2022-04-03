<?php

namespace App;

class Page extends Model
{
    use HasUser, HasSlug, Listable;

    protected $with = ['partner'];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function (Page $page) {
            $page->removeCriterias();
        });
    }


    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    protected function relationMappers()
    {
        return [
            'partner_id'    => [
                'table'     => 'partners',
                'column'    => 'name'
            ]
        ];
    }

    protected function excludedFromFilters()
    {
        return ['partner_id'];
    }

    protected function actions()
    {
        return [
            'edit' => [
                'label' => 'Szerkesztés',
                'style' => 'primary'
            ],
            'delete' => [
                'label' => 'Törlés',
                'style' => 'danger'
            ]
        ];
    }

    public function removeCriterias()
    {
        $pageLoadCriterias = Criteria::getAllFromCache()
            ->whereIn('slug', ['visited_path', 'visited_url'])
            ->pluck('id');

        $groupCriteriasPageLoad = SegmentGroupCriteria::whereIn('criteria_id', $pageLoadCriterias)->get();

        $groupCriteriasForPage = $groupCriteriasPageLoad->filter(function ($x) {
            $parsedValue = json_decode($x->value, true);
            $values = array_values($parsedValue);

            return in_array($this->id, $values);
        });

        $groupCriteriasForPage->each->delete();
    }
}

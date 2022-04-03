<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace App\Services\Segment\CriteriaOptions;


use App\Criteria;

class OptionsFactory
{
    public static function create(Criteria $criteria)
    {
        if (empty($criteria->properties)) return new NoOptions;

        switch ($criteria->slug) {
            case 'visited_url': return new PageOptions('page_id');
            case 'visited_path': return new MultiplePageOptions(['from_page_id', 'to_page_id']);
            default: return new NoOptions;
        }
    }
}

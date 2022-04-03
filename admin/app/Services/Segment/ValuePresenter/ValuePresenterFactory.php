<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace App\Services\Segment\ValuePresenter;

class ValuePresenterFactory
{
    public static function create($field, $value)
    {
        switch ($field) {
            case 'visited_path': return new VisitedPathPresenter($field, $value);
            case 'visited_url': return new VisitedUrlPresenter($field, $value);
            default: return new IdentityPresenter($field, $value);
        }
    }
}

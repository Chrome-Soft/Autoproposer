<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace App\Services\Segment\ValuePresenter;


use App\Page;

class VisitedPathPresenter extends ValuePresenter
{
    public function present()
    {
        $normalizedValue = json_decode($this->value, true);
        $fromPage = Page::find($normalizedValue['from_page_id']);
        $toPage = Page::find($normalizedValue['to_page_id']);

        return "{$fromPage->name} -> {$toPage->name}";
    }
}

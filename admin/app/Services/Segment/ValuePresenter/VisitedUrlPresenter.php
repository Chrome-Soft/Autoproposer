<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace App\Services\Segment\ValuePresenter;


use App\Page;

class VisitedUrlPresenter extends ValuePresenter
{
    public function present()
    {
        $normalizedValue = json_decode($this->value, true);
        return Page::find($normalizedValue['page_id'])->name;
    }
}

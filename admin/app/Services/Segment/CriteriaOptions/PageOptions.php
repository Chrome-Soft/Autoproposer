<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace App\Services\Segment\CriteriaOptions;

use App\Page;

class PageOptions implements CriteriaOptions
{
    private $index;

    public function __construct($index)
    {
        $this->index = $index;
    }

    public function get()
    {
        $pages = Page::all();
        return [
            "{$this->index}"  =>  $pages
        ];
    }
}

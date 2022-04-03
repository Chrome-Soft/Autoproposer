<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace App\Services\Segment\CriteriaOptions;


class MultiplePageOptions implements CriteriaOptions
{
    private $pageOptions;

    public function __construct(array $indices)
    {
        foreach ($indices as $index) {
            $this->pageOptions[$index] = new PageOptions($index);
        }
    }

    public function get()
    {
        $result = [];
        foreach ($this->pageOptions as $index => $item) {
            $result[$index] = $item->get()[$index];
        }

        return $result;
    }
}

<?php

namespace App;

trait HasBoolType
{
    public function getBoolTypeAsText()
    {
        if ($this->bool_type == '') return '';
        return $this->bool_type == 'or' ? 'VAGY' : 'ÉS';
    }
}
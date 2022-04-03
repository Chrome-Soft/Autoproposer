<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace App\Services\Segment\ValuePresenter;


abstract class ValuePresenter
{
    // criteria slug
    protected $field;
    protected $value;

    public function __construct($field, $value)
    {

        $this->field = $field;
        $this->value = $value;
    }

    abstract public function present();
}

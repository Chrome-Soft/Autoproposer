<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace App\Services\Segment\ValuePresenter;


class IdentityPresenter extends ValuePresenter
{
    public function present()
    {
        return $this->value;
    }
}

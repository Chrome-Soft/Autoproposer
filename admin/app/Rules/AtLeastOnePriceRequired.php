<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class AtLeastOnePriceRequired implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $notNullValues = array_filter($value, function ($x) {
            return $x != null;
        });

        return count($notNullValues) >= 1;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'At least one price required';
    }
}

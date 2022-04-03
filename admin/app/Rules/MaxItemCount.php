<?php

namespace App\Rules;

use App\Proposer;
use Illuminate\Contracts\Validation\Rule;

class MaxItemCount implements Rule
{
    /**
     * @var Proposer
     */

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
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
        $proposer = Proposer::where('id', $value)->withCount('items')->first();
        if ($proposer->items_count >= $proposer->max_item_number)
            return false;

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The proposer cannot have more items';
    }
}

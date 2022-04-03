<?php

namespace App\Rules;

use App\Product;
use Illuminate\Contracts\Validation\Rule;

class UniqueProductBatch implements Rule
{
    private $failedNames = [];

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
     * @param  mixed  $names
     * @return bool
     */
    public function passes($attribute, $names)
    {
        // Ha megtudom oldani, hogy innen visszaadjam valahogy az invalid indexeket, akkor le tudom kezelni, hogy csak a többivel dolgozzon tovább az import
        $allNames = Product::select('name')->get()->map(function ($x) { return $x->name; });
        foreach ($names as $name) {
            if ($allNames->contains($name)) $this->failedNames[] = $name;
            if (empty($name)) $this->failedNames[] = $name;
        }

        return count($this->failedNames) == 0;
    }

    /**
     * Get the validation error message.
     *
     */
    public function message()
    {
        return $this->failedNames;
    }
}

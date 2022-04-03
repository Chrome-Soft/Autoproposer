<?php

namespace App\Policies;

use App\User;
use App\ProductAttribute;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductAttributePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the product attribute.
     *
     * @param  \App\User  $user
     * @param  \App\ProductAttribute  $productAttribute
     * @return mixed
     */
    public function view(User $user, ProductAttribute $productAttribute)
    {
        //
    }

    /**
     * Determine whether the user can create product attributes.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the product attribute.
     *
     * @param  \App\User  $user
     * @param  \App\ProductAttribute  $productAttribute
     * @return mixed
     */
    public function update(User $user, ProductAttribute $productAttribute)
    {
        if (is_null($productAttribute->user_id)) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can delete the product attribute.
     *
     * @param  \App\User  $user
     * @param  \App\ProductAttribute  $productAttribute
     * @return mixed
     */
    public function delete(User $user, ProductAttribute $productAttribute)
    {
        if (is_null($productAttribute->user_id)) {
            return false;
        }

        return $user->id == $productAttribute->user_id;
    }

    /**
     * Determine whether the user can restore the product attribute.
     *
     * @param  \App\User  $user
     * @param  \App\ProductAttribute  $productAttribute
     * @return mixed
     */
    public function restore(User $user, ProductAttribute $productAttribute)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the product attribute.
     *
     * @param  \App\User  $user
     * @param  \App\ProductAttribute  $productAttribute
     * @return mixed
     */
    public function forceDelete(User $user, ProductAttribute $productAttribute)
    {
        //
    }
}

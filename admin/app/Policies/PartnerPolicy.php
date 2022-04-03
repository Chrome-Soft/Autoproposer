<?php

namespace App\Policies;

use App\User;
use App\Partner;
use Illuminate\Auth\Access\HandlesAuthorization;

class PartnerPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the partner.
     *
     * @param  \App\User  $user
     * @param  \App\Partner  $partner
     * @return mixed
     */
    public function view(User $user, Partner $partner)
    {
        //
    }

    /**
     * Determine whether the user can create partners.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the partner.
     *
     * @param  \App\User  $user
     * @param  \App\Partner  $partner
     * @return mixed
     */
    public function update(User $user, Partner $partner)
    {
        return true;
    }

    /**
     * Determine whether the user can delete the partner.
     *
     * @param  \App\User  $user
     * @param  \App\Partner  $partner
     * @return mixed
     */
    public function delete(User $user, Partner $partner)
    {
        return $user->id == $partner->user_id;
    }

    /**
     * Determine whether the user can restore the partner.
     *
     * @param  \App\User  $user
     * @param  \App\Partner  $partner
     * @return mixed
     */
    public function restore(User $user, Partner $partner)
    {
        return $user->id == $partner->user_id && $partner->trashed();
    }

    /**
     * Determine whether the user can permanently delete the partner.
     *
     * @param  \App\User  $user
     * @param  \App\Partner  $partner
     * @return mixed
     */
    public function forceDelete(User $user, Partner $partner)
    {
        //
    }
}

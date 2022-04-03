<?php

namespace App\Policies;

use App\User;
use App\Proposer;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProposerPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the proposer.
     *
     * @param  \App\User  $user
     * @param  \App\Proposer  $proposer
     * @return mixed
     */
    public function view(User $user, Proposer $proposer)
    {
        //
    }

    /**
     * Determine whether the user can create proposers.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the proposer.
     *
     * @param  \App\User  $user
     * @param  \App\Proposer  $proposer
     * @return mixed
     */
    public function update(User $user, Proposer $proposer)
    {
        return true;
    }

    /**
     * Determine whether the user can delete the proposer.
     *
     * @param  \App\User  $user
     * @param  \App\Proposer  $proposer
     * @return mixed
     */
    public function delete(User $user, Proposer $proposer)
    {
        return $user->id == $proposer->user_id;
    }

    /**
     * Determine whether the user can restore the proposer.
     *
     * @param  \App\User  $user
     * @param  \App\Proposer  $proposer
     * @return mixed
     */
    public function restore(User $user, Proposer $proposer)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the proposer.
     *
     * @param  \App\User  $user
     * @param  \App\Proposer  $proposer
     * @return mixed
     */
    public function forceDelete(User $user, Proposer $proposer)
    {
        //
    }
}

<?php

namespace App\Policies;

use App\User;
use App\Segment;
use Illuminate\Auth\Access\HandlesAuthorization;

class SegmentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the segment.
     *
     * @param  \App\User  $user
     * @param  \App\Segment  $segment
     * @return mixed
     */
    public function view(User $user, Segment $segment)
    {
        //
    }

    /**
     * Determine whether the user can create segments.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the segment.
     *
     * @param  \App\User  $user
     * @param  \App\Segment  $segment
     * @return mixed
     */
    public function update(User $user, Segment $segment)
    {
        return true;
    }

    /**
     * Determine whether the user can delete the segment.
     *
     * @param  \App\User  $user
     * @param  \App\Segment  $segment
     * @return mixed
     */
    public function delete(User $user, Segment $segment)
    {
        return $this->isNotDefault($segment) && $user->id == $segment->user->id;
    }

    /**
     * Determine whether the user can restore the segment.
     *
     * @param  \App\User  $user
     * @param  \App\Segment  $segment
     * @return mixed
     */
    public function restore(User $user, Segment $segment)
    {
        return $this->isNotDefault($segment) && $user->id == $segment->user_id && $segment->trashed();
    }

    /**
     * Determine whether the user can permanently delete the segment.
     *
     * @param  \App\User  $user
     * @param  \App\Segment  $segment
     * @return mixed
     */
    public function forceDelete(User $user, Segment $segment)
    {
        //
    }

    public function segmentify(User $user, Segment $segment)
    {
        return $this->isNotDefault($segment) && $user->email == env('ADMIN_EMAIL', 'admin@admin.hu');
    }

    protected function isNotDefault(Segment $segment)
    {
        if ($segment->slug == Segment::DEFAULT_SEGMENT) return false;
        return true;
    }
}

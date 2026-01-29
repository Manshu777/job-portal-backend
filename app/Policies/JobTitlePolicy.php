<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\JobTitle;
use App\Models\User;

class JobTitlePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any JobTitle');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, JobTitle $jobtitle): bool
    {
        return $user->checkPermissionTo('view JobTitle');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create JobTitle');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, JobTitle $jobtitle): bool
    {
        return $user->checkPermissionTo('update JobTitle');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, JobTitle $jobtitle): bool
    {
        return $user->checkPermissionTo('delete JobTitle');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any JobTitle');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, JobTitle $jobtitle): bool
    {
        return $user->checkPermissionTo('restore JobTitle');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any JobTitle');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, JobTitle $jobtitle): bool
    {
        return $user->checkPermissionTo('replicate JobTitle');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder JobTitle');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, JobTitle $jobtitle): bool
    {
        return $user->checkPermissionTo('force-delete JobTitle');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any JobTitle');
    }
}

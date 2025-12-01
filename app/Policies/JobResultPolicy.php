<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\JobResult;
use App\Models\User;

class JobResultPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any JobResult');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, JobResult $jobresult): bool
    {
        return $user->checkPermissionTo('view JobResult');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create JobResult');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, JobResult $jobresult): bool
    {
        return $user->checkPermissionTo('update JobResult');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, JobResult $jobresult): bool
    {
        return $user->checkPermissionTo('delete JobResult');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any JobResult');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, JobResult $jobresult): bool
    {
        return $user->checkPermissionTo('restore JobResult');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any JobResult');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, JobResult $jobresult): bool
    {
        return $user->checkPermissionTo('replicate JobResult');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder JobResult');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, JobResult $jobresult): bool
    {
        return $user->checkPermissionTo('force-delete JobResult');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any JobResult');
    }
}

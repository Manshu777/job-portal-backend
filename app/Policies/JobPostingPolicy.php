<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\JobPosting;
use App\Models\User;

class JobPostingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any JobPosting');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, JobPosting $jobposting): bool
    {
        return $user->checkPermissionTo('view JobPosting');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create JobPosting');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, JobPosting $jobposting): bool
    {
        return $user->checkPermissionTo('update JobPosting');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, JobPosting $jobposting): bool
    {
        return $user->checkPermissionTo('delete JobPosting');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any JobPosting');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, JobPosting $jobposting): bool
    {
        return $user->checkPermissionTo('restore JobPosting');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any JobPosting');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, JobPosting $jobposting): bool
    {
        return $user->checkPermissionTo('replicate JobPosting');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder JobPosting');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, JobPosting $jobposting): bool
    {
        return $user->checkPermissionTo('force-delete JobPosting');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any JobPosting');
    }
}

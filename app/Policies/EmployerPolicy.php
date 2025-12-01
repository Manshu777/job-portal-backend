<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Employer;
use App\Models\User;

class EmployerPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any Employer');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Employer $employer): bool
    {
        return $user->checkPermissionTo('view Employer');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create Employer');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Employer $employer): bool
    {
        return $user->checkPermissionTo('update Employer');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Employer $employer): bool
    {
        return $user->checkPermissionTo('delete Employer');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any Employer');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Employer $employer): bool
    {
        return $user->checkPermissionTo('restore Employer');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any Employer');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, Employer $employer): bool
    {
        return $user->checkPermissionTo('replicate Employer');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder Employer');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Employer $employer): bool
    {
        return $user->checkPermissionTo('force-delete Employer');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any Employer');
    }
}

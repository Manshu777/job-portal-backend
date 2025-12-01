<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Qualification;
use App\Models\User;

class QualificationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any Qualification');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Qualification $qualification): bool
    {
        return $user->checkPermissionTo('view Qualification');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create Qualification');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Qualification $qualification): bool
    {
        return $user->checkPermissionTo('update Qualification');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Qualification $qualification): bool
    {
        return $user->checkPermissionTo('delete Qualification');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any Qualification');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Qualification $qualification): bool
    {
        return $user->checkPermissionTo('restore Qualification');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any Qualification');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, Qualification $qualification): bool
    {
        return $user->checkPermissionTo('replicate Qualification');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder Qualification');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Qualification $qualification): bool
    {
        return $user->checkPermissionTo('force-delete Qualification');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any Qualification');
    }
}

<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Candidate;
use App\Models\User;

class CandidatePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any Candidate');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Candidate $candidate): bool
    {
        return $user->checkPermissionTo('view Candidate');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create Candidate');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Candidate $candidate): bool
    {
        return $user->checkPermissionTo('update Candidate');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Candidate $candidate): bool
    {
        return $user->checkPermissionTo('delete Candidate');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any Candidate');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Candidate $candidate): bool
    {
        return $user->checkPermissionTo('restore Candidate');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any Candidate');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, Candidate $candidate): bool
    {
        return $user->checkPermissionTo('replicate Candidate');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder Candidate');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Candidate $candidate): bool
    {
        return $user->checkPermissionTo('force-delete Candidate');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any Candidate');
    }
}

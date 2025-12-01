<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\CandidateEducation;
use App\Models\User;

class CandidateEducationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any CandidateEducation');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CandidateEducation $candidateeducation): bool
    {
        return $user->checkPermissionTo('view CandidateEducation');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create CandidateEducation');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CandidateEducation $candidateeducation): bool
    {
        return $user->checkPermissionTo('update CandidateEducation');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CandidateEducation $candidateeducation): bool
    {
        return $user->checkPermissionTo('delete CandidateEducation');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any CandidateEducation');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CandidateEducation $candidateeducation): bool
    {
        return $user->checkPermissionTo('restore CandidateEducation');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any CandidateEducation');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, CandidateEducation $candidateeducation): bool
    {
        return $user->checkPermissionTo('replicate CandidateEducation');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder CandidateEducation');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CandidateEducation $candidateeducation): bool
    {
        return $user->checkPermissionTo('force-delete CandidateEducation');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any CandidateEducation');
    }
}

<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\CandidateSkill;
use App\Models\User;

class CandidateSkillPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any CandidateSkill');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CandidateSkill $candidateskill): bool
    {
        return $user->checkPermissionTo('view CandidateSkill');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create CandidateSkill');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CandidateSkill $candidateskill): bool
    {
        return $user->checkPermissionTo('update CandidateSkill');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CandidateSkill $candidateskill): bool
    {
        return $user->checkPermissionTo('delete CandidateSkill');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any CandidateSkill');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CandidateSkill $candidateskill): bool
    {
        return $user->checkPermissionTo('restore CandidateSkill');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any CandidateSkill');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, CandidateSkill $candidateskill): bool
    {
        return $user->checkPermissionTo('replicate CandidateSkill');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder CandidateSkill');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CandidateSkill $candidateskill): bool
    {
        return $user->checkPermissionTo('force-delete CandidateSkill');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any CandidateSkill');
    }
}

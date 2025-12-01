<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\CreditTransaction;
use App\Models\User;

class CreditTransactionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any CreditTransaction');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CreditTransaction $credittransaction): bool
    {
        return $user->checkPermissionTo('view CreditTransaction');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create CreditTransaction');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CreditTransaction $credittransaction): bool
    {
        return $user->checkPermissionTo('update CreditTransaction');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CreditTransaction $credittransaction): bool
    {
        return $user->checkPermissionTo('delete CreditTransaction');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any CreditTransaction');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CreditTransaction $credittransaction): bool
    {
        return $user->checkPermissionTo('restore CreditTransaction');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any CreditTransaction');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, CreditTransaction $credittransaction): bool
    {
        return $user->checkPermissionTo('replicate CreditTransaction');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder CreditTransaction');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CreditTransaction $credittransaction): bool
    {
        return $user->checkPermissionTo('force-delete CreditTransaction');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any CreditTransaction');
    }
}

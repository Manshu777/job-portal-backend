<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\CitiesData;
use App\Models\User;

class CitiesDataPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any CitiesData');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CitiesData $citiesdata): bool
    {
        return $user->checkPermissionTo('view CitiesData');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create CitiesData');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CitiesData $citiesdata): bool
    {
        return $user->checkPermissionTo('update CitiesData');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CitiesData $citiesdata): bool
    {
        return $user->checkPermissionTo('delete CitiesData');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any CitiesData');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CitiesData $citiesdata): bool
    {
        return $user->checkPermissionTo('restore CitiesData');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any CitiesData');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, CitiesData $citiesdata): bool
    {
        return $user->checkPermissionTo('replicate CitiesData');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder CitiesData');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CitiesData $citiesdata): bool
    {
        return $user->checkPermissionTo('force-delete CitiesData');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any CitiesData');
    }
}

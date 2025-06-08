<?php

namespace App\Policies;

use App\Models\GuideTrip;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class GuideTripPolicy
{
    use HandlesAuthorization;

    public function viewAny(User|Admin $user): bool
    {
        // Example rule: both can see it, or apply role-specific logic
        return true;
    }

    public function view(User|Admin $user, GuideTrip $trip): bool
    {
        // Admins can view all, users only their own
        if ($user instanceof Admin) {
            return true;
        }

        return $trip->guide_id === $user->id;
    }

    public function create(User|Admin $user): bool
    {
        // Maybe only Users can create?
        return true;
    }

    public function update(User|Admin $user, GuideTrip $trip): bool
    {
        if ($user instanceof Admin) {
            return true;
        }

        return $trip->guide_id === $user->id;
    }

    public function delete(User|Admin $user, GuideTrip $trip): bool
    {
        if ($user instanceof Admin) {
            return true;
        }

        return $trip->guide_id === $user->id;
    }


    /**
     * Determine whether the admin can bulk delete.
     */
    public function deleteAny(User|Admin $user): bool
    {
        return $user->can('delete_any_guide::trip');
    }

    /**
     * Determine whether the admin can permanently delete the model.
     */
    public function forceDelete(User|Admin $user, GuideTrip $guideTripPolicy): bool
    {
        return $user->can('force_delete_guide::trip');
    }

    /**
     * Determine whether the admin can permanently bulk delete models.
     */
    public function forceDeleteAny(User|Admin $user): bool
    {
        return $user->can('force_delete_any_guide::trip');
    }

    /**
     * Determine whether the admin can restore the model.
     */
    public function restore(User|Admin $user, GuideTrip $guideTripPolicy): bool
    {
        return $user->can('restore_guide::trip');
    }

    /**
     * Determine whether the admin can bulk restore models.
     */
    public function restoreAny(User|Admin $user): bool
    {
        return $user->can('restore_any_guide::trip');
    }

    /**
     * Determine whether the admin can replicate the model.
     */
    public function replicate(User|Admin $user, GuideTrip $guideTripPolicy): bool
    {
        return $user->can('replicate_guide::trip');
    }

    /**
     * Determine whether the admin can reorder models.
     */
    public function reorder(User|Admin $user): bool
    {
        return $user->can('reorder_guide::trip');
    }



}

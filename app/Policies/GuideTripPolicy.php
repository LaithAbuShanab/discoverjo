<?php

namespace App\Policies;

use App\Models\GuideTrip;
use App\Models\Admin;
use Illuminate\Auth\Access\HandlesAuthorization;

class GuideTripPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the admin can view any models.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->can('view_any_guide::trip');
    }

    /**
     * Determine whether the admin can view the model.
     */
    public function view(Admin $admin, GuideTrip $guideTripPolicy): bool
    {
        return $admin->can('view_guide::trip');
    }

    /**
     * Determine whether the admin can create models.
     */
    public function create(Admin $admin): bool
    {
        return $admin->can('create_guide::trip');
    }

    /**
     * Determine whether the admin can update the model.
     */
    public function update(Admin $admin, GuideTrip $guideTripPolicy): bool
    {
        return $admin->can('update_guide::trip');
    }

    /**
     * Determine whether the admin can delete the model.
     */
    public function delete(Admin $admin, GuideTrip $guideTripPolicy): bool
    {
        return $admin->can('delete_guide::trip');
    }

    /**
     * Determine whether the admin can bulk delete.
     */
    public function deleteAny(Admin $admin): bool
    {
        return $admin->can('delete_any_guide::trip');
    }

    /**
     * Determine whether the admin can permanently delete the model.
     */
    public function forceDelete(Admin $admin, GuideTrip $guideTripPolicy): bool
    {
        return $admin->can('force_delete_guide::trip');
    }

    /**
     * Determine whether the admin can permanently bulk delete models.
     */
    public function forceDeleteAny(Admin $admin): bool
    {
        return $admin->can('force_delete_any_guide::trip');
    }

    /**
     * Determine whether the admin can restore the model.
     */
    public function restore(Admin $admin, GuideTrip $guideTripPolicy): bool
    {
        return $admin->can('restore_guide::trip');
    }

    /**
     * Determine whether the admin can bulk restore models.
     */
    public function restoreAny(Admin $admin): bool
    {
        return $admin->can('restore_any_guide::trip');
    }

    /**
     * Determine whether the admin can replicate the model.
     */
    public function replicate(Admin $admin, GuideTrip $guideTripPolicy): bool
    {
        return $admin->can('replicate_guide::trip');
    }

    /**
     * Determine whether the admin can reorder models.
     */
    public function reorder(Admin $admin): bool
    {
        return $admin->can('reorder_guide::trip');
    }
}

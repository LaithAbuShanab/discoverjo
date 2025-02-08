<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the admin can view any models.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->can('view_any_user');
    }

    /**
     * Determine whether the admin can view the model.
     */
    public function view(Admin $admin, User $user): bool
    {
        return $admin->can('view_user');
    }

    /**
     * Determine whether the admin can create models.
     */
    public function create(Admin $admin): bool
    {
        return $admin->can('create_user');
    }

    /**
     * Determine whether the admin can update the model.
     */
    public function update(Admin $admin, User $user): bool
    {
        return $admin->can('update_user');
    }

    /**
     * Determine whether the admin can delete the model.
     */
    public function delete(Admin $admin, User $user): bool
    {
        return $admin->can('delete_user');
    }

    /**
     * Determine whether the admin can bulk delete.
     */
    public function deleteAny(Admin $admin): bool
    {
        return $admin->can('delete_any_user');
    }

    /**
     * Determine whether the admin can permanently delete.
     */
    public function forceDelete(Admin $admin, User $user): bool
    {
        return $admin->can('{{ ForceDelete }}');
    }

    /**
     * Determine whether the admin can permanently bulk delete.
     */
    public function forceDeleteAny(Admin $admin): bool
    {
        return $admin->can('{{ ForceDeleteAny }}');
    }

    /**
     * Determine whether the admin can restore.
     */
    public function restore(Admin $admin, User $user): bool
    {
        return $admin->can('{{ Restore }}');
    }

    /**
     * Determine whether the admin can bulk restore.
     */
    public function restoreAny(Admin $admin): bool
    {
        return $admin->can('{{ RestoreAny }}');
    }

    /**
     * Determine whether the admin can replicate.
     */
    public function replicate(Admin $admin, User $user): bool
    {
        return $admin->can('{{ Replicate }}');
    }

    /**
     * Determine whether the admin can reorder.
     */
    public function reorder(Admin $admin): bool
    {
        return $admin->can('{{ Reorder }}');
    }
}

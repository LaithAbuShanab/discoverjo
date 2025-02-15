<?php

namespace App\Policies;

use App\Models\LegalDocument;
use App\Models\Admin;
use Illuminate\Auth\Access\HandlesAuthorization;

class LegalDocumentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the admin can view any models.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->can('view_any_legal::document');
    }

    /**
     * Determine whether the admin can view the model.
     */
    public function view(Admin $admin, LegalDocument $legalDocument): bool
    {
        return $admin->can('view_legal::document');
    }

    /**
     * Determine whether the admin can create models.
     */
    public function create(Admin $admin): bool
    {
        return $admin->can('create_legal::document');
    }

    /**
     * Determine whether the admin can update the model.
     */
    public function update(Admin $admin, LegalDocument $legalDocument): bool
    {
        return $admin->can('update_legal::document');
    }

    /**
     * Determine whether the admin can delete the model.
     */
    public function delete(Admin $admin, LegalDocument $legalDocument): bool
    {
        return $admin->can('delete_legal::document');
    }

    /**
     * Determine whether the admin can bulk delete.
     */
    public function deleteAny(Admin $admin): bool
    {
        return $admin->can('delete_any_legal::document');
    }

    /**
     * Determine whether the admin can permanently delete the model.
     */
    public function forceDelete(Admin $admin, LegalDocument $legalDocument): bool
    {
        return $admin->can('force_delete_legal::document');
    }

    /**
     * Determine whether the admin can permanently bulk delete models.
     */
    public function forceDeleteAny(Admin $admin): bool
    {
        return $admin->can('force_delete_any_legal::document');
    }

    /**
     * Determine whether the admin can restore the model.
     */
    public function restore(Admin $admin, LegalDocument $legalDocument): bool
    {
        return $admin->can('restore_legal::document');
    }

    /**
     * Determine whether the admin can bulk restore models.
     */
    public function restoreAny(Admin $admin): bool
    {
        return $admin->can('restore_any_legal::document');
    }

    /**
     * Determine whether the admin can replicate the model.
     */
    public function replicate(Admin $admin, LegalDocument $legalDocument): bool
    {
        return $admin->can('replicate_legal::document');
    }

    /**
     * Determine whether the admin can reorder models.
     */
    public function reorder(Admin $admin): bool
    {
        return $admin->can('reorder_legal::document');
    }
}

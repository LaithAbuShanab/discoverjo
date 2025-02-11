<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\SuggestionPlace;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SuggestionPlacePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the admin can view any models.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->can('view_any_suggestion::place');
    }

    /**
     * Determine whether the admin can view the model.
     */
    public function view(Admin $admin, SuggestionPlace $suggestionPlace): bool
    {
        return $admin->can('view_suggestion::place');
    }

    /**
     * Determine whether the admin can create models.
     */
    public function create(Admin $admin): bool
    {
        return $admin->can('create_suggestion::place');
    }

    /**
     * Determine whether the admin can update the model.
     */
    public function update(Admin $admin, SuggestionPlace $suggestionPlace): bool
    {
        return $admin->can('update_suggestion::place');
    }

    /**
     * Determine whether the admin can delete the model.
     */
    public function delete(Admin $admin, SuggestionPlace $suggestionPlace): bool
    {
        return $admin->can('delete_suggestion::place');
    }

    /**
     * Determine whether the admin can bulk delete.
     */
    public function deleteAny(Admin $admin): bool
    {
        return $admin->can('delete_any_suggestion::place');
    }

    /**
     * Determine whether the admin can permanently delete the model.
     */
    public function forceDelete(Admin $admin, SuggestionPlace $suggestionPlace): bool
    {
        return $admin->can('force_delete_suggestion::place');
    }

    /**
     * Determine whether the admin can permanently bulk delete models.
     */
    public function forceDeleteAny(Admin $admin): bool
    {
        return $admin->can('force_delete_any_suggestion::place');
    }

    /**
     * Determine whether the admin can restore the model.
     */
    public function restore(Admin $admin, SuggestionPlace $suggestionPlace): bool
    {
        return $admin->can('restore_suggestion::place');
    }

    /**
     * Determine whether the admin can bulk restore models.
     */
    public function restoreAny(Admin $admin): bool
    {
        return $admin->can('restore_any_suggestion::place');
    }

    /**
     * Determine whether the admin can replicate the model.
     */
    public function replicate(Admin $admin, SuggestionPlace $suggestionPlace): bool
    {
        return $admin->can('replicate_suggestion::place');
    }

    /**
     * Determine whether the admin can reorder models.
     */
    public function reorder(Admin $admin): bool
    {
        return $admin->can('reorder_suggestion::place');
    }
}

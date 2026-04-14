<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->hasRole('super_admin') || $authUser->can('view_any_role');
    }

    public function view(AuthUser $authUser, Role $role): bool
    {
        return $authUser->hasRole('super_admin') || $authUser->can('view_role');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->hasRole('super_admin') || $authUser->can('create_role');
    }

    public function update(AuthUser $authUser, Role $role): bool
    {
        return $authUser->hasRole('super_admin') || $authUser->can('update_role');
    }

    public function delete(AuthUser $authUser, Role $role): bool
    {
        return $authUser->hasRole('super_admin') || $authUser->can('delete_role');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->hasRole('super_admin') || $authUser->can('delete_any_role');
    }

    public function restore(AuthUser $authUser, Role $role): bool
    {
        return $authUser->hasRole('super_admin') || $authUser->can('restore_role');
    }

    public function forceDelete(AuthUser $authUser, Role $role): bool
    {
        return $authUser->hasRole('super_admin') || $authUser->can('force_delete_role');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->hasRole('super_admin') || $authUser->can('force_delete_any_role');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->hasRole('super_admin') || $authUser->can('restore_any_role');
    }

    public function replicate(AuthUser $authUser, Role $role): bool
    {
        return $authUser->hasRole('super_admin') || $authUser->can('replicate_role');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->hasRole('super_admin') || $authUser->can('reorder_role');
    }
}

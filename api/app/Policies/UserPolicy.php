<?php

namespace App\Policies;

use App\Models\User;
use App\Support\Roles;

/**
 * User management is restricted to the super admin.
 *
 * Filament delegates resource authorization to Laravel policies, so these
 * methods gate the user resource pages, its actions and its navigation item.
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(Roles::SUPER_ADMIN);
    }

    public function view(User $user, User $model): bool
    {
        return $user->hasRole(Roles::SUPER_ADMIN);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(Roles::SUPER_ADMIN);
    }

    public function update(User $user, User $model): bool
    {
        return $user->hasRole(Roles::SUPER_ADMIN);
    }

    /**
     * Never let a super admin delete their own account by accident: doing so
     * could leave the shop without an administrator.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->hasRole(Roles::SUPER_ADMIN) && ! $user->is($model);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole(Roles::SUPER_ADMIN);
    }

    public function restore(User $user, User $model): bool
    {
        return $user->hasRole(Roles::SUPER_ADMIN);
    }

    public function restoreAny(User $user): bool
    {
        return $user->hasRole(Roles::SUPER_ADMIN);
    }

    public function forceDelete(User $user, User $model): bool
    {
        return $user->hasRole(Roles::SUPER_ADMIN) && ! $user->is($model);
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->hasRole(Roles::SUPER_ADMIN);
    }
}

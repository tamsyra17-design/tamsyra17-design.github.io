<?php

namespace App\Policies;

use App\Models\Setting;
use App\Models\User;
use App\Support\Roles;

/**
 * Settings include private secrets such as payment keys, so only the super
 * admin may see or change them.
 */
class SettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(Roles::SUPER_ADMIN);
    }

    public function view(User $user, Setting $setting): bool
    {
        return $user->hasRole(Roles::SUPER_ADMIN);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(Roles::SUPER_ADMIN);
    }

    public function update(User $user, Setting $setting): bool
    {
        return $user->hasRole(Roles::SUPER_ADMIN);
    }

    public function delete(User $user, Setting $setting): bool
    {
        return $user->hasRole(Roles::SUPER_ADMIN);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole(Roles::SUPER_ADMIN);
    }
}

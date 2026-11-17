<?php

namespace Database\Seeders;

use App\Support\Roles;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * The staff roles this shop supports.
     *
     * @var list<string>
     */
    public const ROLES = Roles::ALL;

    /**
     * Seed the staff roles. Safe to run more than once.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::ROLES as $role) {
            Role::findOrCreate($role, 'web');
        }
    }
}

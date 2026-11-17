<?php

namespace Database\Seeders;

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
    public const ROLES = [
        'super_admin',
        'catalog_manager',
        'order_manager',
        'rental_manager',
        'content_manager',
        'support',
    ];

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

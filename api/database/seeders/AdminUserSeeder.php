<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the initial super admin staff account from the environment.
     *
     * Credentials come from ADMIN_EMAIL / ADMIN_PASSWORD. When either is
     * missing the seeder is a no-op, so no default password is ever shipped.
     */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if (blank($email) || blank($password)) {
            $this->command?->warn('ADMIN_EMAIL and ADMIN_PASSWORD must both be set; skipping super admin seeding.');

            return;
        }

        $admin = User::firstOrCreate(
            ['email' => mb_strtolower(trim($email))],
            [
                'name' => env('ADMIN_NAME', 'Super Admin'),
                'password' => $password,
            ]
        );

        // Only promote an account this seeder just created. Re-seeding must not
        // silently grant super_admin to a pre-existing customer who happens to
        // own the configured address.
        if (! $admin->wasRecentlyCreated) {
            $this->command?->warn('A user already exists for ADMIN_EMAIL; leaving its roles untouched.');

            return;
        }

        Role::findOrCreate('super_admin', 'web');

        $admin->assignRole('super_admin');
    }
}

<?php

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('seeds staff roles', function () {
    $this->seed(RoleSeeder::class);
    expect(Role::pluck('name')->all())
        ->toContain('super_admin', 'catalog_manager', 'rental_manager');
});

it('seeds exactly the six staff roles without duplicates', function () {
    $this->seed(RoleSeeder::class);
    $this->seed(RoleSeeder::class);

    expect(Role::pluck('name')->all())->toEqualCanonicalizing([
        'super_admin',
        'catalog_manager',
        'order_manager',
        'rental_manager',
        'content_manager',
        'support',
    ]);
});

it('seeds one super admin staff user from the environment', function () {
    $this->seed(RoleSeeder::class);
    $this->seed(AdminUserSeeder::class);

    $admin = User::where('email', env('ADMIN_EMAIL'))->first();

    expect($admin)->not->toBeNull()
        ->and($admin->hasRole('super_admin'))->toBeTrue()
        ->and(User::role('super_admin')->count())->toBe(1);
});

it('seeds the super admin idempotently', function () {
    $this->seed(RoleSeeder::class);
    $this->seed(AdminUserSeeder::class);
    $this->seed(AdminUserSeeder::class);

    expect(User::role('super_admin')->count())->toBe(1);
});

it('gives customers no staff role on registration', function () {
    $this->seed(RoleSeeder::class);

    $this->postJson('/api/v1/auth/register', [
        'name' => 'Salim', 'email' => 'salim@example.com',
        'phone' => '+96890000000', 'password' => 'secret123',
    ])->assertCreated();

    $customer = User::where('email', 'salim@example.com')->firstOrFail();

    expect($customer->roles)->toBeEmpty();
});

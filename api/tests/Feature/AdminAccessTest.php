<?php

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

it('blocks a customer from the admin panel', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin')->assertForbidden();
});

it('allows a super admin into the admin panel', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $this->actingAs($admin)->get('/admin')->assertOk();
});

it('redirects guests to the admin login page', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
    $this->get('/admin/login')->assertOk();
});

it('lets every staff role into the panel', function (string $role) {
    $staff = User::factory()->create();
    $staff->assignRole($role);

    $this->actingAs($staff)->get('/admin')->assertOk();
})->with([
    'super_admin',
    'catalog_manager',
    'order_manager',
    'rental_manager',
    'content_manager',
    'support',
]);

it('lets a super admin manage users', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $this->actingAs($admin)->get('/admin/users')->assertOk();
});

it('localizes the user resource labels', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $this->actingAs($admin)
        ->get('/admin/users')
        ->assertOk()
        ->assertSee('المستخدمون')
        ->assertSee('البريد الإلكتروني');

    $this->actingAs($admin)
        ->get('/admin/users?locale=en')
        ->assertOk()
        ->assertSee('Users')
        ->assertSee('Email address');
});

it('stops a super admin from deleting their own account', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $other = User::factory()->create();
    $other->assignRole('super_admin');

    expect($admin->can('delete', $admin))->toBeFalse()
        ->and($admin->can('delete', $other))->toBeTrue();
});

it('forbids non-super staff from the user resource', function (string $role) {
    $staff = User::factory()->create();
    $staff->assignRole($role);

    $this->actingAs($staff)->get('/admin/users')->assertForbidden();
})->with([
    'catalog_manager',
    'order_manager',
    'rental_manager',
    'content_manager',
    'support',
]);

it('renders the admin panel in Arabic RTL by default', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $this->actingAs($admin)
        ->get('/admin')
        ->assertOk()
        ->assertSee('lang="ar"', false)
        ->assertSee('dir="rtl"', false);
});

it('renders the admin panel in English LTR on request', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $this->actingAs($admin)
        ->get('/admin?locale=en')
        ->assertOk()
        ->assertSee('lang="en"', false)
        ->assertSee('dir="ltr"', false);
});

it('still returns JSON 401 to API guests', function () {
    // The Filament panel introduces its own login route; API guests must keep
    // getting a JSON 401 instead of being redirected to the panel login.
    $this->get('/api/v1/user')->assertUnauthorized();
    $this->getJson('/api/v1/user')->assertUnauthorized();
});

<?php

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('loads the admin dashboard for staff', function () {
    $this->seed(RoleSeeder::class);
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    $this->actingAs($admin)->get('/admin')->assertOk();
});

it('refuses a customer', function () {
    $customer = User::factory()->create();
    $this->actingAs($customer)->get('/admin')->assertForbidden();
});

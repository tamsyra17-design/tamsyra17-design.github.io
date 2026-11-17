<?php

use App\Models\Category;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('super_admin');
});

it('renders the category admin pages', function () {
    $category = Category::factory()->create(['name' => ['ar' => 'خناجر', 'en' => 'Khanjars']]);

    $this->actingAs($this->admin)->get('/admin/categories')
        ->assertOk()
        ->assertSee('Khanjars');
    $this->actingAs($this->admin)->get('/admin/categories/create')->assertOk();
    $this->actingAs($this->admin)->get("/admin/categories/{$category->id}/edit")->assertOk();
});

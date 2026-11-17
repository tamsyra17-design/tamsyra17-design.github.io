<?php

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists active categories with translations', function () {
    Category::factory()->create(['name' => ['ar' => 'خناجر', 'en' => 'Khanjars']]);
    Category::factory()->create(['is_active' => false]);

    $this->getJson('/api/v1/categories')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name.ar', 'خناجر')
        ->assertJsonPath('data.0.name.en', 'Khanjars');
});

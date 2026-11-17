<?php

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('exposes model fields on the product resource', function () {
    $p = Product::factory()->create([
        'model_3d_url' => 'https://example.test/model.glb',
        'model_3d_poster' => 'https://example.test/model.webp',
    ]);

    $this->getJson("/api/v1/products/{$p->slug}")
        ->assertOk()
        ->assertJsonPath('data.model_3d_url', 'https://example.test/model.glb')
        ->assertJsonPath('data.model_3d_poster', 'https://example.test/model.webp');
});

it('resolves stored upload paths to absolute public-disk urls', function () {
    $p = Product::factory()->create([
        'model_3d_url' => 'models/khanjar.glb',
        'model_3d_poster' => 'models/khanjar.webp',
    ]);

    $this->getJson("/api/v1/products/{$p->slug}")
        ->assertOk()
        ->assertJsonPath('data.model_3d_url', Storage::disk('public')->url('models/khanjar.glb'))
        ->assertJsonPath('data.model_3d_poster', Storage::disk('public')->url('models/khanjar.webp'));
});

it('returns null model fields when none are set', function () {
    $p = Product::factory()->create();

    $this->getJson("/api/v1/products/{$p->slug}")
        ->assertOk()
        ->assertJsonPath('data.model_3d_url', null)
        ->assertJsonPath('data.model_3d_poster', null);
});

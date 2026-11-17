<?php

use App\Models\Product;
use App\Models\RentalPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns a rental quote', function () {
    $p = Product::factory()->create(['slug' => 'khanjar', 'is_rentable' => true]);
    RentalPolicy::factory()->create(['is_default' => true]);

    $this->postJson('/api/v1/rental/quote', [
        'product_slug' => 'khanjar', 'from' => '2026-12-01', 'to' => '2026-12-03',
    ])->assertOk()->assertJsonStructure(['data' => ['days', 'total_baisa']]);
});

it('rejects a duration outside the policy limits', function () {
    Product::factory()->create(['slug' => 'khanjar', 'is_rentable' => true]);
    RentalPolicy::factory()->create(['is_default' => true, 'min_days' => 2, 'max_days' => 5]);

    $this->postJson('/api/v1/rental/quote', [
        'product_slug' => 'khanjar', 'from' => '2026-12-01', 'to' => '2026-12-01',
    ])->assertUnprocessable();
});

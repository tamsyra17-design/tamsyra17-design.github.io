<?php

use App\Models\ShippingRate;
use App\Models\ShippingZone;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns the rate for a governorate and weight', function () {
    $zone = ShippingZone::factory()->create(['governorate' => 'Muscat']);
    ShippingRate::factory()->create([
        'shipping_zone_id' => $zone->id, 'min_weight_grams' => 0,
        'max_weight_grams' => 5000, 'rate_baisa' => 2000,
    ]);

    $this->getJson('/api/v1/shipping/rates?governorate=Muscat&weight_grams=1200&subtotal_baisa=10000')
        ->assertOk()->assertJsonPath('data.rate_baisa', 2000);
});

it('returns free shipping when the subtotal exceeds the threshold', function () {
    $zone = ShippingZone::factory()->create(['governorate' => 'Muscat']);
    ShippingRate::factory()->create([
        'shipping_zone_id' => $zone->id, 'min_weight_grams' => 0,
        'max_weight_grams' => 5000, 'rate_baisa' => 2000, 'free_over_baisa' => 5000,
    ]);

    $this->getJson('/api/v1/shipping/rates?governorate=Muscat&weight_grams=1200&subtotal_baisa=10000')
        ->assertOk()->assertJsonPath('data.rate_baisa', 0)
        ->assertJsonPath('data.free', true);
});

it('returns 404 when no zone matches the governorate', function () {
    $this->getJson('/api/v1/shipping/rates?governorate=Narnia&weight_grams=1200&subtotal_baisa=10000')
        ->assertNotFound();
});
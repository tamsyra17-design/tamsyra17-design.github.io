<?php

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingRate;
use App\Models\ShippingZone;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// api/tests/Feature/OrderApiTest.php
it('creates a COD order and decrements stock', function () {
    $p = Product::factory()->create(['price_baisa' => 10000, 'weight_grams' => 500]);
    $v = ProductVariant::factory()->create(['product_id' => $p->id, 'sale_stock' => 5]);
    $zone = ShippingZone::factory()->create(['governorate' => 'Muscat']);
    ShippingRate::factory()->create(['shipping_zone_id' => $zone->id, 'rate_baisa' => 2000, 'min_weight_grams' => 0, 'max_weight_grams' => 5000]);

    $this->postJson('/api/v1/orders', [
        'customer' => ['name' => 'Salim', 'phone' => '+96890000000', 'email' => 's@example.com'],
        'address' => ['governorate' => 'Muscat', 'wilayat' => 'Muscat', 'line1' => 'Al Khuwair'],
        'items' => [['variant_id' => $v->id, 'qty' => 2]],
        'payment_method' => 'cod',
    ])->assertCreated()->assertJsonPath('data.total_baisa', 22000);

    expect($v->fresh()->sale_stock)->toBe(3);
});

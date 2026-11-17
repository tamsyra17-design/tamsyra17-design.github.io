<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingRate;
use App\Models\ShippingZone;
use App\Services\Exceptions\ShippingUnavailableException;
use App\Services\ShippingCalculator;
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

function orderFixture(array $variant = [], array $rate = []): ProductVariant
{
    $p = Product::factory()->create(['price_baisa' => 10000, 'weight_grams' => 500]);
    $v = ProductVariant::factory()->create(['product_id' => $p->id, 'sale_stock' => 5, ...$variant]);
    $zone = ShippingZone::factory()->create(['governorate' => 'Muscat']);
    ShippingRate::factory()->create(['shipping_zone_id' => $zone->id, 'rate_baisa' => 2000, 'min_weight_grams' => 0, 'max_weight_grams' => 5000, ...$rate]);

    return $v;
}

function orderPayload(ProductVariant $v, int $qty = 1, array $overrides = []): array
{
    return array_replace([
        'customer' => ['name' => 'Salim', 'phone' => '+96890000000', 'email' => 's@example.com'],
        'address' => ['governorate' => 'Muscat', 'wilayat' => 'Muscat', 'line1' => 'Al Khuwair'],
        'items' => [['variant_id' => $v->id, 'qty' => $qty]],
        'payment_method' => 'cod',
    ], $overrides);
}

it('snapshots items and records a pending COD payment', function () {
    $v = orderFixture();

    $number = $this->postJson('/api/v1/orders', orderPayload($v, 2))
        ->assertCreated()
        ->assertJsonPath('data.status', 'pending')
        ->assertJsonPath('data.payment_status', 'pending')
        ->assertJsonPath('data.items.0.sku', $v->sku)
        ->assertJsonPath('data.items.0.line_total_baisa', 20000)
        ->assertJsonPath('data.payment.method', 'cod')
        ->json('data.number');

    $order = Order::where('number', $number)->sole();
    expect($order->payments()->sole())
        ->status->toBe('pending')
        ->amount_baisa->toBe(22000);
});

it('returns bank instructions for a bank transfer order', function () {
    config(['payments.bank_transfer.iban' => 'OM810180000001299123456']);
    $v = orderFixture();

    $response = $this->postJson('/api/v1/orders', orderPayload($v, 1, ['payment_method' => 'bank_transfer']))
        ->assertCreated()
        ->assertJsonPath('data.payment.instructions.iban', 'OM810180000001299123456')
        ->assertJsonPath('data.payment.instructions.amount_baisa', 12000);

    expect($response->json('data.payment.instructions.reference'))->toBe($response->json('data.number'));
});

it('uses the variant price over the product price', function () {
    $v = orderFixture(['price_baisa' => 15000]);

    $this->postJson('/api/v1/orders', orderPayload($v))
        ->assertCreated()
        ->assertJsonPath('data.subtotal_baisa', 15000);
});

it('rejects an order that exceeds stock and leaves stock untouched', function () {
    $v = orderFixture(['sale_stock' => 1]);

    $this->postJson('/api/v1/orders', orderPayload($v, 2))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('items.0.qty');

    expect($v->fresh()->sale_stock)->toBe(1)
        ->and(Order::count())->toBe(0);
});

it('rejects an inactive variant', function () {
    $v = orderFixture(['is_active' => false]);

    $this->postJson('/api/v1/orders', orderPayload($v))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('items.0.variant_id');
});

it('rejects an unknown payment method', function () {
    $v = orderFixture();

    $this->postJson('/api/v1/orders', orderPayload($v, 1, ['payment_method' => 'bitcoin']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('payment_method');
});

it('rejects a governorate with no shipping zone', function () {
    $v = orderFixture();
    $payload = orderPayload($v);
    $payload['address']['governorate'] = 'Dhofar';

    $this->postJson('/api/v1/orders', $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('address.governorate');

    expect($v->fresh()->sale_stock)->toBe(5);
});

it('ships free once the subtotal reaches the threshold', function () {
    $v = orderFixture([], ['free_over_baisa' => 20000]);

    $this->postJson('/api/v1/orders', orderPayload($v, 2))
        ->assertCreated()
        ->assertJsonPath('data.shipping_baisa', 0)
        ->assertJsonPath('data.total_baisa', 20000);
});

it('picks the rate whose weight band contains the parcel', function () {
    $zone = ShippingZone::factory()->create(['governorate' => 'Muscat']);
    ShippingRate::factory()->create(['shipping_zone_id' => $zone->id, 'min_weight_grams' => 0, 'max_weight_grams' => 1000, 'rate_baisa' => 1500]);
    ShippingRate::factory()->create(['shipping_zone_id' => $zone->id, 'min_weight_grams' => 1001, 'max_weight_grams' => null, 'rate_baisa' => 4000]);
    $calc = new ShippingCalculator;

    expect($calc->rateFor('Muscat', 1000, 0))->toBe(1500)
        ->and($calc->rateFor('Muscat', 25000, 0))->toBe(4000);
});

it('throws when no weight band matches', function () {
    $zone = ShippingZone::factory()->create(['governorate' => 'Muscat']);
    ShippingRate::factory()->create(['shipping_zone_id' => $zone->id, 'min_weight_grams' => 0, 'max_weight_grams' => 1000]);

    (new ShippingCalculator)->rateFor('Muscat', 1001, 0);
})->throws(ShippingUnavailableException::class);

it('shows an order to a guest with the matching phone', function () {
    $v = orderFixture();
    $number = $this->postJson('/api/v1/orders', orderPayload($v, 1))->json('data.number');

    $this->getJson("/api/v1/orders/{$number}?phone=".urlencode('+968 9000 0000'))
        ->assertOk()
        ->assertJsonPath('data.number', $number)
        ->assertJsonPath('data.address.line1', 'Al Khuwair')
        ->assertJsonPath('data.customer.email', 's@example.com')
        ->assertJsonPath('data.items.0.qty', 1);
});

it('hides an order behind a 404 without the matching phone', function (?string $phone) {
    $v = orderFixture();
    $number = $this->postJson('/api/v1/orders', orderPayload($v, 1))->json('data.number');

    $url = "/api/v1/orders/{$number}".($phone === null ? '' : '?phone='.urlencode($phone));

    $response = $this->getJson($url)->assertNotFound();

    expect($response->getContent())
        ->not->toContain('Al Khuwair')
        ->not->toContain('s@example.com');
})->with([
    'missing phone' => [null],
    'empty phone' => [''],
    'wrong phone' => ['+96899999999'],
]);

it('returns 404 for an unknown order number', function () {
    $this->getJson('/api/v1/orders/ORD-000000-XXXXXX?phone=96890000000')->assertNotFound();
});

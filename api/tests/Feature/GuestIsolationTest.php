<?php

use App\Models\Order;
use App\Models\Rental;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('does not reveal an order with the wrong phone', function () {
    $order = Order::factory()->create(['number' => 'ORD-1', 'phone' => '+96890000000']);

    $this->getJson('/api/v1/orders/ORD-1?phone=+96891111111')->assertNotFound();
    $this->getJson('/api/v1/orders/ORD-1?phone=+96890000000')->assertOk();
});

it('does not reveal a rental with the wrong phone', function () {
    $rental = Rental::factory()->create(['number' => 'RNT-1', 'phone' => '+96890000000']);

    $this->getJson('/api/v1/rentals/RNT-1?phone=+96891111111')->assertNotFound();
    $this->getJson('/api/v1/rentals/RNT-1?phone=+96890000000')->assertOk();
});

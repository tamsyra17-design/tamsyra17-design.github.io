<?php

use App\Models\Product;
use App\Models\RentalBooking;
use App\Models\RentalPolicy;
use App\Models\RentalUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a rental and reserves a unit atomically', function () {
    $p = Product::factory()->create(['slug' => 'khanjar', 'is_rentable' => true]);
    RentalUnit::factory()->create(['product_id' => $p->id]);
    RentalPolicy::factory()->create(['is_default' => true, 'deposit_mode' => 'charge']);

    $this->postJson('/api/v1/rentals', [
        'product_slug' => 'khanjar', 'from' => '2026-12-01', 'to' => '2026-12-03',
        'customer' => ['name' => 'Salim', 'phone' => '+96890000000', 'email' => 's@example.com'],
    ])->assertCreated()->assertJsonPath('data.status', 'pending');

    expect(RentalBooking::count())->toBe(1);
});

it('looks up a rental by number and phone', function () {
    $p = Product::factory()->create(['slug' => 'khanjar', 'is_rentable' => true]);
    RentalUnit::factory()->create(['product_id' => $p->id]);
    RentalPolicy::factory()->create(['is_default' => true, 'deposit_mode' => 'charge']);

    $res = $this->postJson('/api/v1/rentals', [
        'product_slug' => 'khanjar', 'from' => '2026-12-01', 'to' => '2026-12-03',
        'customer' => ['name' => 'Salim', 'phone' => '+96890000000', 'email' => 's@example.com'],
    ])->assertCreated();

    $number = $res->json('data.number');

    $this->getJson("/api/v1/rentals/{$number}?phone=+96890000000")
        ->assertOk()
        ->assertJsonPath('data.number', $number);

    $this->getJson("/api/v1/rentals/{$number}?phone=+96899999999")
        ->assertNotFound();
});

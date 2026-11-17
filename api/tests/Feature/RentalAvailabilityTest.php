<?php

use App\Models\Product;
use App\Models\RentalBooking;
use App\Models\RentalUnit;
use App\Services\RentalAvailabilityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('excludes booked units and respects buffer days', function () {
    $p = Product::factory()->create(['is_rentable' => true]);
    $u1 = RentalUnit::factory()->create(['product_id' => $p->id]);
    RentalUnit::factory()->create(['product_id' => $p->id]);
    RentalBooking::create([
        'rental_unit_id' => $u1->id,
        'start_date' => '2026-12-01',
        'end_date' => '2026-12-03',
    ]);

    $available = app(RentalAvailabilityService::class)
        ->availableUnits($p, Carbon::parse('2026-12-03'), Carbon::parse('2026-12-05'));

    expect($available)->toHaveCount(1);
});

it('exposes availability over HTTP', function () {
    $p = Product::factory()->create(['is_rentable' => true, 'slug' => 'musar-test']);
    RentalUnit::factory()->create(['product_id' => $p->id]);

    $this->getJson('/api/v1/rental/products/musar-test/availability?from=2026-12-10&to=2026-12-12')
        ->assertOk()
        ->assertJsonPath('available', 1);
});

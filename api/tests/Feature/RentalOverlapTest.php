<?php

use App\Models\Product;
use App\Models\RentalBooking;
use App\Models\RentalPolicy;
use App\Models\RentalUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('rejects an overlapping rental booking', function () {
    $p = Product::factory()->create(['slug' => 'khanjar', 'is_rentable' => true]);
    $u = RentalUnit::factory()->create(['product_id' => $p->id]);
    RentalPolicy::factory()->create(['is_default' => true, 'buffer_days' => 0]);
    RentalBooking::create(['rental_unit_id' => $u->id, 'start_date' => '2026-12-01', 'end_date' => '2026-12-03']);

    $this->postJson('/api/v1/rentals', [
        'product_slug' => 'khanjar', 'from' => '2026-12-02', 'to' => '2026-12-04',
        'customer' => ['name' => 'Salim', 'phone' => '+96890000000'],
    ])->assertUnprocessable();
});

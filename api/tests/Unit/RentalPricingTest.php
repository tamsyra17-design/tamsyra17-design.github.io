<?php

use App\Models\Product;
use App\Models\RentalPolicy;
use App\Services\RentalPricingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('charges base fee plus extra days', function () {
    $p = Product::factory()->make();
    $policy = new RentalPolicy([
        'base_days' => 2, 'base_fee_baisa' => 10000, 'extra_daily_baisa' => 3000,
        'deposit_baisa' => 20000, 'return_day_inclusive' => false,
    ]);

    $quote = app(RentalPricingService::class)
        ->quote($p, Carbon::parse('2026-12-01'), Carbon::parse('2026-12-05'), $policy);

    expect($quote->days)->toBe(4);
    expect($quote->extra_days)->toBe(2);
    expect($quote->total_baisa)->toBe(36000); // 10000 + 2*3000 + 20000 deposit
});

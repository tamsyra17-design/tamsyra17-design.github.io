<?php

use App\Actions\RecordRentalReturn;
use App\Actions\ReleaseDeposit;
use App\Models\Rental;
use App\Models\RentalDeposit;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('records a return and releases the deposit', function () {
    $rental = Rental::factory()->create(['status' => 'picked_up', 'deposit_baisa' => 20000]);
    $deposit = RentalDeposit::factory()->create(['rental_id' => $rental->id, 'status' => 'charged']);

    app(RecordRentalReturn::class)->execute($rental, ['condition' => 'good']);
    app(ReleaseDeposit::class)->execute($rental);

    expect($rental->fresh()->status)->toBe('completed');
    expect($deposit->fresh()->status)->toBe('released');
});

<?php

namespace Database\Factories;

use App\Models\Rental;
use App\Models\RentalDeposit;
use Illuminate\Database\Eloquent\Factories\Factory;

class RentalDepositFactory extends Factory
{
    protected $model = RentalDeposit::class;

    public function definition(): array
    {
        return [
            'rental_id' => Rental::factory(),
            'amount_baisa' => 20000,
            'mode' => 'charge',
            'status' => 'pending',
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Rental;
use App\Models\RentalPolicy;
use Illuminate\Database\Eloquent\Factories\Factory;

class RentalFactory extends Factory
{
    protected $model = Rental::class;

    public function definition(): array
    {
        return [
            'number' => 'RNT-'.now()->format('ymd').'-'.strtoupper(bin2hex(random_bytes(3))),
            'user_id' => null,
            'rental_policy_id' => RentalPolicy::factory(),
            'customer_name' => $this->faker->name(),
            'phone' => '+96890000000',
            'email' => $this->faker->safeEmail(),
            'address' => null,
            'start_date' => now()->addDays(1)->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'days' => 2,
            'base_fee_baisa' => 5000,
            'extra_fee_baisa' => 0,
            'deposit_baisa' => 20000,
            'total_baisa' => 25000,
            'status' => Rental::STATUS_PENDING,
            'payment_status' => Rental::PAYMENT_PENDING,
            'payment_method' => 'cod',
            'placed_at' => now(),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'payable_type' => Order::class,
            'payable_id' => Order::factory(),
            'gateway' => 'cod',
            'method' => 'cod',
            'amount_baisa' => fake()->numberBetween(1, 100) * 1000,
            'status' => Payment::STATUS_PENDING,
            'transaction_ref' => null,
            'payload' => null,
            'paid_at' => null,
        ];
    }
}

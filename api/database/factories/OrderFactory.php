<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(1, 100) * 1000;
        $shipping = 2000;

        return [
            'number' => 'ORD-'.Str::upper(Str::random(10)),
            'user_id' => null,
            'customer_name' => fake()->name(),
            'phone' => '+9689'.fake()->numerify('#######'),
            'email' => fake()->safeEmail(),
            'address' => [
                'governorate' => 'Muscat',
                'wilayat' => 'Bawshar',
                'line1' => fake()->streetAddress(),
                'line2' => null,
                'postal_code' => null,
            ],
            'subtotal_baisa' => $subtotal,
            'shipping_baisa' => $shipping,
            'total_baisa' => $subtotal + $shipping,
            'status' => Order::STATUS_PENDING,
            'payment_status' => Order::PAYMENT_PENDING,
            'payment_method' => 'cod',
            'placed_at' => now(),
        ];
    }
}

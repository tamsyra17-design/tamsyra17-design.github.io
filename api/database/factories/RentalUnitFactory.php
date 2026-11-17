<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\RentalUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RentalUnit>
 */
class RentalUnitFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory()->state(['is_rentable' => true]),
            'code' => 'U-'.fake()->unique()->numerify('#####'),
            'status' => 'available',
            'notes' => null,
        ];
    }

    public function maintenance(): static
    {
        return $this->state(fn () => ['status' => 'maintenance']);
    }

    public function retired(): static
    {
        return $this->state(fn () => ['status' => 'retired']);
    }
}

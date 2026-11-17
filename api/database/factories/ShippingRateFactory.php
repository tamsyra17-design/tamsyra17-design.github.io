<?php

namespace Database\Factories;

use App\Models\ShippingRate;
use App\Models\ShippingZone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShippingRate>
 */
class ShippingRateFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'shipping_zone_id' => ShippingZone::factory(),
            'name' => 'Standard',
            'min_weight_grams' => 0,
            'max_weight_grams' => 5000,
            'rate_baisa' => fake()->numberBetween(1, 10) * 500,
            'free_over_baisa' => null,
        ];
    }

    public function freeOver(int $baisa): static
    {
        return $this->state(fn () => ['free_over_baisa' => $baisa]);
    }
}

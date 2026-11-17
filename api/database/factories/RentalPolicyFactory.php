<?php

namespace Database\Factories;

use App\Models\RentalPolicy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RentalPolicy>
 */
class RentalPolicyFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Policy '.fake()->unique()->word(),
            'base_days' => 1,
            'base_fee_baisa' => 5000,
            'extra_daily_baisa' => 3000,
            'weekly_fee_baisa' => null,
            'deposit_baisa' => 20000,
            'deposit_mode' => 'charge',
            'return_day_inclusive' => false,
            'buffer_days' => 1,
            'min_days' => 1,
            'max_days' => 30,
            'late_fee_per_day_baisa' => 3000,
            'damage_fee_notes' => null,
            'require_id' => false,
            'require_contract' => false,
            'inspection_days' => 2,
            'is_default' => false,
        ];
    }

    public function asDefault(): static
    {
        return $this->state(fn () => ['is_default' => true]);
    }
}

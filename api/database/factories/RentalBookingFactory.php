<?php

namespace Database\Factories;

use App\Models\RentalBooking;
use App\Models\RentalUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RentalBooking>
 */
class RentalBookingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = now()->addDays(fake()->numberBetween(1, 60))->startOfDay();

        return [
            'rental_unit_id' => RentalUnit::factory(),
            'start_date' => $start->toDateString(),
            'end_date' => $start->copy()->addDays(fake()->numberBetween(1, 7))->toDateString(),
            'rental_id' => null,
        ];
    }
}

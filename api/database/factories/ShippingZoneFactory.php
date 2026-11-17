<?php

namespace Database\Factories;

use App\Models\ShippingZone;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ShippingZone>
 */
class ShippingZoneFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // A random suffix keeps the unique governorate clear of real names
        // such as "Muscat" that tests create explicitly.
        $governorate = 'Zone '.Str::upper(Str::random(8));

        return [
            'name' => ['ar' => 'منطقة '.$governorate, 'en' => $governorate],
            'governorate' => $governorate,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}

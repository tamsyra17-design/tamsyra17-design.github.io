<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'parent_id' => null,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(6)),
            'name' => ['ar' => $name, 'en' => Str::title($name)],
            'description' => null,
            'image_path' => null,
            'sort_order' => 0,
            'is_active' => true,
            'is_rental_only' => false,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function rentalOnly(): static
    {
        return $this->state(fn () => ['is_rental_only' => true]);
    }
}

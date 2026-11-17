<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'category_id' => Category::factory(),
            'sku' => 'SKU-'.Str::upper(Str::random(10)),
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(6)),
            'name' => ['ar' => $name, 'en' => Str::title($name)],
            'short_description' => ['ar' => fake()->sentence(), 'en' => fake()->sentence()],
            'description' => ['ar' => fake()->paragraph(), 'en' => fake()->paragraph()],
            'price_baisa' => fake()->numberBetween(1, 500) * 500,
            'is_active' => true,
            'is_rentable' => false,
            'is_featured' => false,
            'weight_grams' => fake()->numberBetween(100, 5000),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function rentable(): static
    {
        return $this->state(fn () => ['is_rentable' => true]);
    }

    public function featured(): static
    {
        return $this->state(fn () => ['is_featured' => true]);
    }

    /**
     * Rentable with no sale price.
     */
    public function rentalOnly(): static
    {
        return $this->state(fn () => ['is_rentable' => true, 'price_baisa' => null]);
    }
}

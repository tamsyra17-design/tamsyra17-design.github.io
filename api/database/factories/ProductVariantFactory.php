<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $size = fake()->randomElement(['S', 'M', 'L', 'XL']);

        return [
            'product_id' => Product::factory(),
            'sku' => 'VAR-'.Str::upper(Str::random(10)),
            'name' => ['ar' => 'مقاس '.$size, 'en' => 'Size '.$size],
            'price_baisa' => fake()->numberBetween(1, 500) * 500,
            'sale_stock' => fake()->numberBetween(0, 20),
            'attributes' => ['size' => $size],
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn () => ['sale_stock' => 0]);
    }
}

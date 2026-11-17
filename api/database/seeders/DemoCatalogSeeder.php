<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\RentalPolicy;
use App\Models\RentalUnit;
use App\Models\ShippingRate;
use App\Models\ShippingZone;
use Illuminate\Database\Seeder;

/**
 * Demo content for local development and smoke testing.
 *
 * Run with `php artisan db:seed --class=DemoCatalogSeeder` (or as part of
 * `DatabaseSeeder` if you uncomment the call there). Idempotent: it only
 * creates rows whose slugs are missing, so re-running is safe.
 */
class DemoCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = $this->seedCategories();
        $this->seedProducts($categories);
        $this->seedRentalPolicy();
        $this->seedShipping();
        $this->seedRentalUnits();
    }

    /**
     * @return array<string, Category>
     */
    private function seedCategories(): array
    {
        $list = [
            ['slug' => 'musar', 'name' => ['ar' => 'مُصَرّ', 'en' => 'Musar'], 'rental_only' => true],
            ['slug' => 'shawls', 'name' => ['ar' => 'شالات', 'en' => 'Shawls'], 'rental_only' => true],
            ['slug' => 'khanjar', 'name' => ['ar' => 'خناجر', 'en' => 'Khanjars']],
            ['slug' => 'saif', 'name' => ['ar' => 'سيوف', 'en' => 'Saifs']],
            ['slug' => 'bisht', 'name' => ['ar' => 'بشت', 'en' => 'Bishts'], 'rental_only' => true],
            ['slug' => 'asa', 'name' => ['ar' => 'عصا', 'en' => 'Asa'], 'rental_only' => true],
            ['slug' => 'jewellery', 'name' => ['ar' => 'مجوهرات', 'en' => 'Jewellery']],
            ['slug' => 'incense', 'name' => ['ar' => 'بخور', 'en' => 'Incense']],
            ['slug' => 'coffee-sets', 'name' => ['ar' => 'أطقم قهوة', 'en' => 'Coffee sets']],
            ['slug' => 'decor', 'name' => ['ar' => 'ديكور', 'en' => 'Decor']],
        ];

        $out = [];
        foreach ($list as $i => $row) {
            $out[$row['slug']] = Category::firstOrCreate(
                ['slug' => $row['slug']],
                [
                    'name' => $row['name'],
                    'sort_order' => $i,
                    'is_active' => true,
                    'is_rental_only' => $row['rental_only'] ?? false,
                ],
            );
        }

        return $out;
    }

    /**
     * @param  array<string, Category>  $categories
     */
    private function seedProducts(array $categories): void
    {
        $items = [
            ['slug' => 'demo-silver-khanjar', 'cat' => 'khanjar', 'name' => ['ar' => 'خنجر فضي', 'en' => 'Silver Khanjar'], 'price' => 45000, 'rentable' => true, 'featured' => true],
            ['slug' => 'demo-gold-khanjar', 'cat' => 'khanjar', 'name' => ['ar' => 'خنجر ذهبي', 'en' => 'Gold Khanjar'], 'price' => 120000, 'rentable' => true, 'featured' => true],
            ['slug' => 'demo-saif', 'cat' => 'saif', 'name' => ['ar' => 'سيف عماني', 'en' => 'Omani Saif'], 'price' => 85000, 'rentable' => false, 'featured' => true],
            ['slug' => 'demo-bisht', 'cat' => 'bisht', 'name' => ['ar' => 'بشت أسود', 'en' => 'Black Bisht'], 'price' => 35000, 'rentable' => true],
            ['slug' => 'demo-musar', 'cat' => 'musar', 'name' => ['ar' => 'مُصَرّ أبيض', 'en' => 'White Musar'], 'price' => null, 'rentable' => true],
            ['slug' => 'demo-shawl', 'cat' => 'shawls', 'name' => ['ar' => 'شال عماني', 'en' => 'Omani Shawl'], 'price' => 8000, 'rentable' => true],
            ['slug' => 'demo-asa', 'cat' => 'asa', 'name' => ['ar' => 'عصا خشبية', 'en' => 'Wooden Asa'], 'price' => 6000, 'rentable' => true],
            ['slug' => 'demo-incense', 'cat' => 'incense', 'name' => ['ar' => 'بخور عماني', 'en' => 'Omani Incense'], 'price' => 2500, 'rentable' => false],
            ['slug' => 'demo-coffee-set', 'cat' => 'coffee-sets', 'name' => ['ar' => 'طقم قهوة', 'en' => 'Coffee Set'], 'price' => 15000, 'rentable' => false],
            ['slug' => 'demo-decor', 'cat' => 'decor', 'name' => ['ar' => 'ديكور تراثي', 'en' => 'Heritage Decor'], 'price' => 18000, 'rentable' => false],
        ];

        foreach ($items as $row) {
            Product::firstOrCreate(
                ['slug' => $row['slug']],
                [
                    'category_id' => $categories[$row['cat']]->id,
                    'sku' => 'SKU-'.strtoupper(bin2hex(random_bytes(5))),
                    'name' => $row['name'],
                    'short_description' => ['ar' => $row['name']['ar'], 'en' => $row['name']['en']],
                    'description' => ['ar' => $row['name']['ar'], 'en' => $row['name']['en']],
                    'price_baisa' => $row['price'],
                    'is_active' => true,
                    'is_rentable' => $row['rentable'],
                    'is_featured' => $row['featured'] ?? false,
                    'weight_grams' => 500,
                ],
            );
        }

        // Pad to 20 products with generic sale items.
        for ($i = 11; $i <= 20; $i++) {
            Product::firstOrCreate(
                ['slug' => "demo-item-{$i}"],
                [
                    'category_id' => $categories['decor']->id,
                    'sku' => 'SKU-'.strtoupper(bin2hex(random_bytes(5))),
                    'name' => ['ar' => "قطعة تراثية {$i}", 'en' => "Heritage Piece {$i}"],
                    'short_description' => ['ar' => 'قطعة تراثية', 'en' => 'Heritage piece'],
                    'description' => ['ar' => 'قطعة تراثية', 'en' => 'Heritage piece'],
                    'price_baisa' => 1000 * $i,
                    'is_active' => true,
                    'is_rentable' => false,
                    'is_featured' => false,
                    'weight_grams' => 300,
                ],
            );
        }
    }

    private function seedRentalPolicy(): void
    {
        RentalPolicy::firstOrCreate(
            ['is_default' => true],
            [
                'name' => 'Default',
                'base_days' => 1,
                'base_fee_baisa' => 5000,
                'extra_daily_baisa' => 3000,
                'deposit_baisa' => 20000,
                'deposit_mode' => 'charge',
                'return_day_inclusive' => false,
                'buffer_days' => 1,
                'min_days' => 1,
                'max_days' => 30,
                'late_fee_per_day_baisa' => 1000,
                'inspection_days' => 2,
            ],
        );
    }

    private function seedShipping(): void
    {
        $governorates = ['Muscat', 'Dhofar', 'Musandam', 'Al Buraimi', 'Ad Dakhiliyah', 'Al Batinah North', 'Al Batinah South', 'Ash Sharqiyah North', 'Ash Sharqiyah South', 'Ad Dhahirah', 'Al Wusta'];

        foreach ($governorates as $gov) {
            $zone = ShippingZone::firstOrCreate(
                ['governorate' => $gov],
                ['name' => ['ar' => $gov, 'en' => $gov], 'is_active' => true],
            );

            ShippingRate::firstOrCreate(
                ['shipping_zone_id' => $zone->id, 'min_weight_grams' => 0, 'max_weight_grams' => 5000],
                ['name' => 'Standard', 'rate_baisa' => 2000, 'free_over_baisa' => 50000],
            );
        }
    }

    private function seedRentalUnits(): void
    {
        $rentable = Product::where('is_rentable', true)->get();

        foreach ($rentable->take(5) as $product) {
            RentalUnit::firstOrCreate(
                ['product_id' => $product->id, 'code' => 'UNIT-1'],
                ['status' => 'available'],
            );
        }
    }
}

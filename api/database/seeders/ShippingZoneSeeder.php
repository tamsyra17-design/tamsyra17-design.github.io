<?php

namespace Database\Seeders;

use App\Models\ShippingZone;
use Illuminate\Database\Seeder;

class ShippingZoneSeeder extends Seeder
{
    /**
     * Oman's eleven governorates, keyed by the English name checkout uses.
     *
     * @var array<string, string>
     */
    public const GOVERNORATES = [
        'Muscat' => 'مسقط',
        'Dhofar' => 'ظفار',
        'Musandam' => 'مسندم',
        'Al Buraimi' => 'البريمي',
        'Ad Dakhiliyah' => 'الداخلية',
        'Al Batinah North' => 'شمال الباطنة',
        'Al Batinah South' => 'جنوب الباطنة',
        'Ash Sharqiyah North' => 'شمال الشرقية',
        'Ash Sharqiyah South' => 'جنوب الشرقية',
        'Ad Dhahirah' => 'الظاهرة',
        'Al Wusta' => 'الوسطى',
    ];

    /**
     * Governorates far from Muscat that cost more to reach.
     *
     * @var list<string>
     */
    private const REMOTE = ['Dhofar', 'Musandam', 'Al Wusta'];

    /**
     * Create the zones and give each a starter pair of weight bands. Zones that
     * already have rates are left alone so admin edits survive a re-run. The
     * amounts are placeholders for the shop owner to adjust.
     */
    public function run(): void
    {
        foreach (self::GOVERNORATES as $en => $ar) {
            $zone = ShippingZone::firstOrCreate(
                ['governorate' => $en],
                ['name' => ['ar' => $ar, 'en' => $en], 'is_active' => true],
            );

            if ($zone->rates()->exists()) {
                continue;
            }

            [$light, $heavy] = match (true) {
                $en === 'Muscat' => [2000, 4000],
                in_array($en, self::REMOTE, true) => [4000, 7000],
                default => [3000, 5000],
            };

            $zone->rates()->createMany([
                ['name' => 'Standard', 'min_weight_grams' => 0, 'max_weight_grams' => 5000, 'rate_baisa' => $light],
                ['name' => 'Heavy', 'min_weight_grams' => 5001, 'max_weight_grams' => null, 'rate_baisa' => $heavy],
            ]);
        }
    }
}

<?php

namespace App\Services;

use App\Models\ShippingRate;
use App\Models\ShippingZone;
use App\Services\Exceptions\ShippingUnavailableException;

class ShippingCalculator
{
    /**
     * Shipping cost in baisa for a parcel of $weightGrams to $governorate.
     *
     * Looks up the active zone for the governorate, then the rate whose weight
     * band (inclusive; a null upper bound is open-ended) contains the weight.
     * Returns 0 when the band's free_over_baisa threshold is reached.
     *
     * @throws ShippingUnavailableException when no zone or band matches.
     */
    public function rateFor(string $governorate, int $weightGrams, int $subtotalBaisa): int
    {
        $zone = ShippingZone::query()
            ->active()
            ->where('governorate', $governorate)
            ->first();

        if ($zone === null) {
            throw ShippingUnavailableException::forGovernorate($governorate);
        }

        $rate = $zone->rates()
            ->where('min_weight_grams', '<=', $weightGrams)
            ->where(fn ($q) => $q->whereNull('max_weight_grams')->orWhere('max_weight_grams', '>=', $weightGrams))
            // Overlapping bands: the narrowest (highest floor) wins, deterministically.
            ->orderByDesc('min_weight_grams')
            ->orderBy('id')
            ->first();

        if (! $rate instanceof ShippingRate) {
            throw ShippingUnavailableException::forWeight($governorate, $weightGrams);
        }

        if ($rate->free_over_baisa !== null && $subtotalBaisa >= $rate->free_over_baisa) {
            return 0;
        }

        return $rate->rate_baisa;
    }
}

<?php

namespace App\Services;

use App\Data\RentalQuote;
use App\Models\Product;
use App\Models\RentalPolicy;
use Carbon\Carbon;

class RentalPricingService
{
    public function quote(Product $product, Carbon $start, Carbon $end, RentalPolicy $policy): RentalQuote
    {
        $days = $start->diffInDays($end) + ($policy->return_day_inclusive ? 1 : 0);
        $extraDays = max(0, $days - $policy->base_days);

        $extraFee = $this->extraFee($extraDays, $policy);

        $total = $policy->base_fee_baisa + $extraFee + $policy->deposit_baisa;

        return new RentalQuote(
            days: $days,
            base_fee_baisa: $policy->base_fee_baisa,
            extra_days: $extraDays,
            extra_fee_baisa: $extraFee,
            deposit_baisa: $policy->deposit_baisa,
            total_baisa: $total,
        );
    }

    private function extraFee(int $extraDays, RentalPolicy $policy): int
    {
        $daily = $extraDays * $policy->extra_daily_baisa;

        if ($policy->weekly_fee_baisa === null || $extraDays < 7) {
            return $daily;
        }

        $weeks = intdiv($extraDays, 7);
        $remainder = $extraDays % 7;
        $weekly = $weeks * $policy->weekly_fee_baisa + $remainder * $policy->extra_daily_baisa;

        return min($daily, $weekly);
    }
}

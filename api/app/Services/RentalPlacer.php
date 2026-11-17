<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Rental;
use App\Models\RentalBooking;
use App\Models\RentalDeposit;
use App\Models\RentalPolicy;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RentalPlacer
{
    public function __construct(
        private readonly RentalPricingService $pricing,
        private readonly RentalNumberGenerator $numbers,
        private readonly RentalAvailabilityService $availability,
    ) {}

    /**
     * Reserve a unit and persist a rental from validated input.
     *
     * @param  array<string, mixed>  $data
     */
    public function place(array $data, ?User $user = null): Rental
    {
        return DB::transaction(function () use ($data, $user): Rental {
            $product = Product::query()
                ->where('slug', $data['product_slug'])
                ->where('is_rentable', true)
                ->where('is_active', true)
                ->firstOrFail();

            $policy = RentalPolicy::default();
            if ($policy === null) {
                throw ValidationException::withMessages([
                    'product_slug' => __('No default rental policy configured.'),
                ]);
            }

            $start = Carbon::parse($data['from']);
            $end = Carbon::parse($data['to']);

            $days = $start->diffInDays($end) + ($policy->return_day_inclusive ? 1 : 0);
            if ($days < $policy->min_days || $days > $policy->max_days) {
                throw ValidationException::withMessages([
                    'from' => __('Rental duration must be between :min and :max days.', [
                        'min' => $policy->min_days,
                        'max' => $policy->max_days,
                    ]),
                ]);
            }

            $available = $this->availability->availableUnits($product, $start, $end);
            $unit = $available->first();

            if ($unit === null) {
                throw ValidationException::withMessages([
                    'product_slug' => __('No rental unit available for the selected dates.'),
                ]);
            }

            $quote = $this->pricing->quote($product, $start, $end, $policy);

            $depositMode = $data['deposit_mode'] ?? $policy->deposit_mode;

            $rental = Rental::create([
                'number' => $this->numbers->generate(),
                'user_id' => $user?->id,
                'rental_policy_id' => $policy->id,
                'customer_name' => $data['customer']['name'],
                'phone' => $data['customer']['phone'],
                'email' => $data['customer']['email'] ?? null,
                'address' => $data['address'] ?? null,
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'days' => $quote->days,
                'base_fee_baisa' => $quote->base_fee_baisa,
                'extra_fee_baisa' => $quote->extra_fee_baisa,
                'deposit_baisa' => $quote->deposit_baisa,
                'total_baisa' => $quote->total_baisa,
                'status' => Rental::STATUS_PENDING,
                'payment_status' => Rental::PAYMENT_PENDING,
                'payment_method' => $data['payment_method'] ?? 'cod',
                'placed_at' => now(),
            ]);

            $rental->items()->create([
                'product_id' => $product->id,
                'rental_unit_id' => $unit->id,
                'name' => ['product' => $product->name],
                'sku' => $product->sku,
                'unit_price_baisa' => $quote->base_fee_baisa + $quote->extra_fee_baisa,
                'qty' => 1,
                'line_total_baisa' => $quote->base_fee_baisa + $quote->extra_fee_baisa,
            ]);

            RentalDeposit::create([
                'rental_id' => $rental->id,
                'amount_baisa' => $quote->deposit_baisa,
                'mode' => $depositMode,
                'status' => 'pending',
            ]);

            RentalBooking::create([
                'rental_unit_id' => $unit->id,
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'rental_id' => $rental->id,
            ]);

            return $rental;
        });
    }
}

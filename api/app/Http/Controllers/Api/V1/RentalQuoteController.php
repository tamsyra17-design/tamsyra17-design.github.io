<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\RentalPolicy;
use App\Services\RentalPricingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RentalQuoteController extends Controller
{
    public function __construct(private readonly RentalPricingService $pricing) {}

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_slug' => ['required', 'string'],
            'from' => ['required', 'date', 'after_or_equal:today'],
            'to' => ['required', 'date', 'after:from'],
        ]);

        $product = Product::query()
            ->where('slug', $data['product_slug'])
            ->where('is_rentable', true)
            ->where('is_active', true)
            ->firstOrFail();

        $policy = RentalPolicy::default();
        abort_if($policy === null, 422, 'No default rental policy configured.');

        $start = Carbon::parse($data['from']);
        $end = Carbon::parse($data['to']);

        $days = $start->diffInDays($end) + ($policy->return_day_inclusive ? 1 : 0);

        if ($days < $policy->min_days || $days > $policy->max_days) {
            return response()->json([
                'message' => 'Rental duration is outside the allowed range.',
                'errors' => ['from' => ['Duration must be between '.$policy->min_days.' and '.$policy->max_days.' days.']],
            ], 422);
        }

        $quote = $this->pricing->quote($product, $start, $end, $policy);

        return response()->json(['data' => $quote->toArray()]);
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\RentalUnit;
use App\Services\RentalAvailabilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class RentalAvailabilityController extends Controller
{
    /**
     * How many units of an active, rentable product are free from `from`
     * (pickup) to `to` (return), with their codes.
     */
    public function show(Request $request, string $slug, RentalAvailabilityService $availability): JsonResponse
    {
        $dates = $request->validate([
            'from' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'to' => ['required', 'date_format:Y-m-d', 'after:from'],
        ]);

        $product = Product::query()
            ->active()
            ->where('is_rentable', true)
            ->where('slug', $slug)
            ->firstOrFail();

        $units = $availability->availableUnits(
            $product,
            Carbon::parse($dates['from']),
            Carbon::parse($dates['to']),
        );

        return response()->json([
            'available' => $units->count(),
            'units' => $units->map(fn (RentalUnit $unit): string => $unit->code)->values(),
        ]);
    }
}

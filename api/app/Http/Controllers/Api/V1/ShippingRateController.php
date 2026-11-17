<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ShippingRate;
use App\Models\ShippingZone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShippingRateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'governorate' => ['required', 'string'],
            'weight_grams' => ['required', 'integer', 'min:0'],
            'subtotal_baisa' => ['required', 'integer', 'min:0'],
        ]);

        $zone = ShippingZone::query()
            ->where('governorate', $data['governorate'])
            ->where('is_active', true)
            ->first();

        abort_if($zone === null, 404);

        $rate = ShippingRate::query()
            ->where('shipping_zone_id', $zone->id)
            ->where('min_weight_grams', '<=', $data['weight_grams'])
            ->where('max_weight_grams', '>=', $data['weight_grams'])
            ->first();

        abort_if($rate === null, 404);

        $free = $rate->free_over_baisa !== null && $data['subtotal_baisa'] >= $rate->free_over_baisa;

        return response()->json([
            'data' => [
                'rate_baisa' => $free ? 0 : $rate->rate_baisa,
                'free' => $free,
            ],
        ]);
    }
}

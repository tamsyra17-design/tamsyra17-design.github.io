<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderPlacer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function store(StoreOrderRequest $request, OrderPlacer $placer): JsonResponse
    {
        // Public route: attach the account only when a valid token is sent.
        $order = $placer->place($request->validated(), $request->user('sanctum'));

        return (new OrderResource($order->load(['items', 'payments'])))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Guest order lookup by number + phone. A wrong or missing phone gets the
     * same 404 as an unknown number, so the response never confirms that an
     * order exists or leaks its contact details.
     */
    public function show(Request $request, string $number): OrderResource
    {
        $phone = self::normalisePhone((string) $request->query('phone', ''));
        $order = Order::query()->where('number', $number)->first();

        abort_if(
            $phone === '' || $order === null || ! hash_equals(self::normalisePhone($order->phone), $phone),
            404,
        );

        return new OrderResource($order->load(['items', 'payments']));
    }

    /**
     * Digits only, so "+968 9000 0000" and "96890000000" match. Phones are
     * stored as entered at checkout.
     */
    private static function normalisePhone(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone) ?? '';
    }
}

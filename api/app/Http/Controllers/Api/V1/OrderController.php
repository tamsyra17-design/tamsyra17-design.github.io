<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Services\OrderPlacer;
use Illuminate\Http\JsonResponse;

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
}

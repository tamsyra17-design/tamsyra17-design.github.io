<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Payments\PaymentManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentWebhookController extends Controller
{
    public function __construct(private readonly PaymentManager $payments) {}

    public function handle(Request $request, string $gateway): JsonResponse
    {
        $driver = $this->payments->gateway($gateway);
        $payment = $driver->handleWebhook($request);

        return response()->json([
            'data' => [
                'payment_id' => $payment->id,
                'status' => $payment->status,
                'transaction_ref' => $payment->transaction_ref,
            ],
        ]);
    }
}

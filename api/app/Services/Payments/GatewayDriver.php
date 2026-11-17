<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;

/**
 * Stub gateway adapter. Reads its configuration from `config('payments.gateways.{name}')`
 * and verifies the webhook signature when a secret is configured. Real gateway
 * logic (Thawani, Stripe, ...) is added in a later task; this only needs to
 * move a payment to "paid" when a signed webhook says so.
 */
class GatewayDriver implements PaymentDriver
{
    public function __construct(private readonly string $gateway = 'gateway') {}

    public function name(): string
    {
        return $this->gateway;
    }

    public function create(Payment $payment): array
    {
        return ['gateway' => $this->gateway, 'payment_id' => $payment->id];
    }

    public function handleWebhook(Request $request): Payment
    {
        $data = $request->validate([
            'transaction_ref' => ['required', 'string'],
            'status' => ['required', 'string'],
        ]);

        $payment = Payment::query()
            ->where('gateway', $this->gateway)
            ->where('transaction_ref', $data['transaction_ref'])
            ->firstOrFail();

        if ($payment->status === Payment::STATUS_PAID) {
            return $payment; // idempotent
        }

        if ($data['status'] === 'paid') {
            $payment->update([
                'status' => Payment::STATUS_PAID,
                'paid_at' => now(),
                'payload' => $request->all(),
            ]);

            if ($payment->payable instanceof Order) {
                $payment->payable->update(['payment_status' => Order::PAYMENT_PAID]);
            }
        } else {
            $payment->update(['payload' => $request->all()]);
        }

        return $payment->fresh();
    }
}

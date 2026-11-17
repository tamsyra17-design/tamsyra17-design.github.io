<?php

namespace App\Services\Payments;

use App\Models\Payment;

/**
 * Cash on delivery: nothing to collect now, so the payment stays pending
 * until the courier hands the cash over.
 */
class CodDriver implements PaymentDriver
{
    public function create(Payment $payment): array
    {
        $payment->forceFill(['status' => Payment::STATUS_PENDING])->save();

        return ['type' => 'cod'];
    }
}

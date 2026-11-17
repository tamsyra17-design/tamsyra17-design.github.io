<?php

namespace App\Services\Payments;

use App\Models\Payment;

interface PaymentDriver
{
    /**
     * Start the payment and return what the customer needs next, e.g. bank
     * instructions or (for gateways, Task 23) a redirect URL.
     *
     * @return array<string, mixed>
     */
    public function create(Payment $payment): array;
}

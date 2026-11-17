<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\Payment;

/**
 * Bank transfer: the payment stays pending and the customer gets the account
 * details plus a reference (the order number) to quote on the transfer.
 */
class BankTransferDriver implements PaymentDriver
{
    public function create(Payment $payment): array
    {
        $payable = $payment->payable;

        $instructions = [
            'bank_name' => config('payments.bank_transfer.bank_name'),
            'account_name' => config('payments.bank_transfer.account_name'),
            'account_number' => config('payments.bank_transfer.account_number'),
            'iban' => config('payments.bank_transfer.iban'),
            'swift' => config('payments.bank_transfer.swift'),
            'amount_baisa' => $payment->amount_baisa,
            'reference' => $payable instanceof Order ? $payable->number : null,
        ];

        $payment->forceFill([
            'status' => Payment::STATUS_PENDING,
            'payload' => ['instructions' => $instructions],
        ])->save();

        return ['type' => 'bank_transfer', 'instructions' => $instructions];
    }
}

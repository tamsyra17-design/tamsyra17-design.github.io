<?php

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('does not double-process a duplicate webhook', function () {
    $order = Order::factory()->create(['payment_status' => 'pending']);
    $payment = Payment::factory()->create([
        'payable_type' => $order::class, 'payable_id' => $order->id,
        'gateway' => 'test', 'transaction_ref' => 'TX-1', 'status' => 'pending',
    ]);

    $payload = ['transaction_ref' => 'TX-1', 'status' => 'paid'];
    $this->postJson('/api/v1/payments/webhook/test', $payload)->assertOk();
    $this->postJson('/api/v1/payments/webhook/test', $payload)->assertOk();

    expect($payment->fresh()->status)->toBe('paid');
    expect(Payment::where('transaction_ref', 'TX-1')->count())->toBe(1);
    expect($order->fresh()->payment_status)->toBe('paid');
});

it('returns 404 for an unknown transaction ref', function () {
    $this->postJson('/api/v1/payments/webhook/test', [
        'transaction_ref' => 'NOPE', 'status' => 'paid',
    ])->assertNotFound();
});

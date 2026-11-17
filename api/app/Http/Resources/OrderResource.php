<?php

namespace App\Http\Resources;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Order
 */
class OrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'number' => $this->number,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
            'customer' => [
                'name' => $this->customer_name,
                'phone' => $this->phone,
                'email' => $this->email,
            ],
            'address' => $this->address,
            'subtotal_baisa' => $this->subtotal_baisa,
            'shipping_baisa' => $this->shipping_baisa,
            'total_baisa' => $this->total_baisa,
            'placed_at' => $this->placed_at?->toIso8601String(),
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn (OrderItem $item): array => [
                'variant_id' => $item->variant_id,
                'name' => $item->name,
                'sku' => $item->sku,
                'unit_price_baisa' => $item->unit_price_baisa,
                'qty' => $item->qty,
                'line_total_baisa' => $item->line_total_baisa,
            ])->all()),
            'payment' => $this->whenLoaded('payments', function (): ?array {
                $payment = $this->payments->sortByDesc('id')->first();

                return $payment instanceof Payment ? [
                    'method' => $payment->method,
                    'status' => $payment->status,
                    'instructions' => $payment->payload['instructions'] ?? null,
                ] : null;
            }),
        ];
    }
}

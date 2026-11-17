<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RentalResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'days' => $this->days,
            'base_fee_baisa' => $this->base_fee_baisa,
            'extra_fee_baisa' => $this->extra_fee_baisa,
            'deposit_baisa' => $this->deposit_baisa,
            'total_baisa' => $this->total_baisa,
            'customer' => [
                'name' => $this->customer_name,
                'phone' => $this->phone,
                'email' => $this->email,
            ],
            'address' => $this->address,
            'items' => $this->whenLoaded('items', fn () => RentalItemResource::collection($this->items)),
            'deposit' => $this->whenLoaded('deposit', fn () => new RentalDepositResource($this->deposit)),
            'placed_at' => $this->placed_at?->toIso8601String(),
        ];
    }
}

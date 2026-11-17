<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RentalDepositResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'amount_baisa' => $this->amount_baisa,
            'mode' => $this->mode,
            'status' => $this->status,
            'transaction_ref' => $this->transaction_ref,
            'paid_at' => $this->paid_at?->toIso8601String(),
        ];
    }
}

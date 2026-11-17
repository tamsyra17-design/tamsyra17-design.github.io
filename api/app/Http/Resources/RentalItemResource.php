<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RentalItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'rental_unit_id' => $this->rental_unit_id,
            'name' => $this->name,
            'sku' => $this->sku,
            'unit_price_baisa' => $this->unit_price_baisa,
            'qty' => $this->qty,
            'line_total_baisa' => $this->line_total_baisa,
        ];
    }
}

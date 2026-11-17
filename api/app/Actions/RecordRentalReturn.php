<?php

namespace App\Actions;

use App\Models\Rental;
use App\Models\RentalReturn;

class RecordRentalReturn
{
    /**
     * @param  array{condition?: string, damage_fee_baisa?: int, late_fee_baisa?: int}  $data
     */
    public function execute(Rental $rental, array $data = []): RentalReturn
    {
        $return = RentalReturn::create([
            'rental_id' => $rental->id,
            'returned_at' => now(),
            'condition_notes' => $data['condition'] ?? null,
            'damage_fee_baisa' => (int) ($data['damage_fee_baisa'] ?? 0),
            'late_fee_baisa' => (int) ($data['late_fee_baisa'] ?? 0),
        ]);

        $rental->update([
            'status' => ($data['condition'] ?? 'good') === 'damaged'
                ? Rental::STATUS_RETURNED
                : Rental::STATUS_COMPLETED,
        ]);

        return $return;
    }
}

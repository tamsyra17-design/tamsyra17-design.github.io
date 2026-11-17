<?php

namespace App\Actions;

use App\Models\Rental;

class ReleaseDeposit
{
    public function execute(Rental $rental): void
    {
        $deposit = $rental->deposit;

        if ($deposit === null) {
            return;
        }

        $damageFee = $rental->returns()->latest()->first()?->damage_fee_baisa ?? 0;

        if ($damageFee > 0 && $damageFee < $deposit->amount_baisa) {
            $deposit->update(['status' => 'released']);

            // partial deduction is recorded via the return's damage_fee_baisa
            return;
        }

        if ($damageFee >= $deposit->amount_baisa) {
            $deposit->update(['status' => 'charged']);

            return;
        }

        $deposit->update(['status' => 'released']);
    }
}

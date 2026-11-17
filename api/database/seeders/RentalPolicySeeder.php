<?php

namespace Database\Seeders;

use App\Models\RentalPolicy;
use Illuminate\Database\Seeder;

class RentalPolicySeeder extends Seeder
{
    /**
     * Create the starter default policy. An existing default is left alone so
     * admin edits survive a re-run. The amounts are placeholders for the shop
     * owner to adjust.
     */
    public function run(): void
    {
        if (RentalPolicy::default() !== null) {
            return;
        }

        RentalPolicy::create([
            'name' => 'Standard',
            'base_days' => 1,
            'base_fee_baisa' => 5000,
            'extra_daily_baisa' => 3000,
            'weekly_fee_baisa' => null,
            'deposit_baisa' => 20000,
            'deposit_mode' => 'charge',
            'return_day_inclusive' => false,
            'buffer_days' => 1,
            'min_days' => 1,
            'max_days' => 30,
            'late_fee_per_day_baisa' => 3000,
            'damage_fee_notes' => null,
            'require_id' => false,
            'require_contract' => false,
            'inspection_days' => 2,
            'is_default' => true,
        ]);
    }
}

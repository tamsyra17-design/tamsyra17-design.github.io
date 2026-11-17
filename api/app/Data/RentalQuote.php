<?php

namespace App\Data;

/**
 * The result of pricing a rental for a date range.
 */
final class RentalQuote
{
    public function __construct(
        public readonly int $days,
        public readonly int $base_fee_baisa,
        public readonly int $extra_days,
        public readonly int $extra_fee_baisa,
        public readonly int $deposit_baisa,
        public readonly int $total_baisa,
    ) {}

    /**
     * @return array<string, int>
     */
    public function toArray(): array
    {
        return [
            'days' => $this->days,
            'base_fee_baisa' => $this->base_fee_baisa,
            'extra_days' => $this->extra_days,
            'extra_fee_baisa' => $this->extra_fee_baisa,
            'deposit_baisa' => $this->deposit_baisa,
            'total_baisa' => $this->total_baisa,
        ];
    }
}

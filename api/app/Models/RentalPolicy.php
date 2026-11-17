<?php

namespace App\Models;

use Database\Factories\RentalPolicyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentalPolicy extends Model
{
    /** @use HasFactory<RentalPolicyFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    public const DEPOSIT_MODES = ['hold', 'charge', 'cash', 'bank_transfer'];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'base_days',
        'base_fee_baisa',
        'extra_daily_baisa',
        'weekly_fee_baisa',
        'deposit_baisa',
        'deposit_mode',
        'return_day_inclusive',
        'buffer_days',
        'min_days',
        'max_days',
        'late_fee_per_day_baisa',
        'damage_fee_notes',
        'require_id',
        'require_contract',
        'inspection_days',
        'is_default',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'base_days' => 1,
        'deposit_baisa' => 0,
        'deposit_mode' => 'charge',
        'return_day_inclusive' => false,
        'buffer_days' => 1,
        'min_days' => 1,
        'max_days' => 30,
        'late_fee_per_day_baisa' => 0,
        'require_id' => false,
        'require_contract' => false,
        'inspection_days' => 2,
        'is_default' => false,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'base_days' => 'integer',
            'base_fee_baisa' => 'integer',
            'extra_daily_baisa' => 'integer',
            'weekly_fee_baisa' => 'integer',
            'deposit_baisa' => 'integer',
            'return_day_inclusive' => 'boolean',
            'buffer_days' => 'integer',
            'min_days' => 'integer',
            'max_days' => 'integer',
            'late_fee_per_day_baisa' => 'integer',
            'require_id' => 'boolean',
            'require_contract' => 'boolean',
            'inspection_days' => 'integer',
            'is_default' => 'boolean',
        ];
    }

    /**
     * Keep a single default: marking a policy as default clears the flag on the others.
     */
    protected static function booted(): void
    {
        static::saved(function (RentalPolicy $policy) {
            if (! $policy->is_default) {
                return;
            }

            static::query()
                ->whereKeyNot($policy->getKey())
                ->where('is_default', true)
                ->update(['is_default' => false]);
        });
    }

    /**
     * The policy for rentable products that don't name their own, or null if none is set.
     */
    public static function default(): ?self
    {
        return static::query()->firstWhere('is_default', true);
    }
}

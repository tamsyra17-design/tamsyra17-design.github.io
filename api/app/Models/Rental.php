<?php

namespace App\Models;

use Database\Factories\RentalFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Rental extends Model
{
    /** @use HasFactory<RentalFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_RETURNED = 'returned';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_PICKED_UP = 'picked_up';

    public const STATUS_COMPLETED = 'completed';

    public const PAYMENT_PENDING = 'pending';

    public const PAYMENT_PAID = 'paid';

    public const PAYMENT_REFUNDED = 'refunded';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'number',
        'user_id',
        'rental_policy_id',
        'customer_name',
        'phone',
        'email',
        'address',
        'start_date',
        'end_date',
        'days',
        'base_fee_baisa',
        'extra_fee_baisa',
        'deposit_baisa',
        'total_baisa',
        'status',
        'payment_status',
        'payment_method',
        'placed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'address' => 'array',
            'start_date' => 'date:Y-m-d',
            'end_date' => 'date:Y-m-d',
            'days' => 'integer',
            'base_fee_baisa' => 'integer',
            'extra_fee_baisa' => 'integer',
            'deposit_baisa' => 'integer',
            'total_baisa' => 'integer',
            'placed_at' => 'datetime',
        ];
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(RentalPolicy::class, 'rental_policy_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(RentalItem::class);
    }

    public function deposit(): HasOne
    {
        return $this->hasOne(RentalDeposit::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(RentalReturn::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

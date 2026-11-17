<?php

namespace App\Models;

use Database\Factories\RentalUnitFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RentalUnit extends Model
{
    /** @use HasFactory<RentalUnitFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    public const STATUSES = ['available', 'maintenance', 'retired'];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'code',
        'status',
        'notes',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'available',
    ];

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return HasMany<RentalBooking, $this>
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(RentalBooking::class);
    }
}

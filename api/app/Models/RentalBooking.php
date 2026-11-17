<?php

namespace App\Models;

use Database\Factories\RentalBookingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A date range a rental unit is out: a customer rental, or an admin block
 * when rental_id is null.
 */
class RentalBooking extends Model
{
    /** @use HasFactory<RentalBookingFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'rental_unit_id',
        'start_date',
        'end_date',
        'rental_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date:Y-m-d',
            'end_date' => 'date:Y-m-d',
            'rental_id' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<RentalUnit, $this>
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(RentalUnit::class, 'rental_unit_id');
    }
}

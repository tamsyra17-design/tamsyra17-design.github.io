<?php

namespace App\Models;

use Database\Factories\RentalReturnFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalReturn extends Model
{
    /** @use HasFactory<RentalReturnFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'rental_id',
        'returned_at',
        'condition_notes',
        'damage_fee_baisa',
        'late_fee_baisa',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'returned_at' => 'datetime',
            'damage_fee_baisa' => 'integer',
            'late_fee_baisa' => 'integer',
        ];
    }

    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }
}

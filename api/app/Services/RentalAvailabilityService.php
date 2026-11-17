<?php

namespace App\Services;

use App\Models\Product;
use App\Models\RentalBooking;
use App\Models\RentalPolicy;
use App\Models\RentalUnit;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;

class RentalAvailabilityService
{
    /**
     * Units of the product that are in service and free from $start (pickup)
     * to $end (return).
     *
     * A unit returned on day X can go out again from X + buffer_days. The same
     * gap is needed after the requested return before the next booking starts.
     * So a booking [S, X] conflicts when S < $end + buffer and X > $start - buffer.
     *
     * @return Collection<int, RentalUnit>
     */
    public function availableUnits(Product $product, Carbon $start, Carbon $end): Collection
    {
        $start = $start->copy()->startOfDay();
        $end = $end->copy()->startOfDay();

        if ($end->lte($start)) {
            throw new InvalidArgumentException('The return date must be after the pickup date.');
        }

        $buffer = $this->bufferDays();

        // whereDate keeps the comparison on the date part: sqlite stores the
        // date cast as "Y-m-d H:i:s", MariaDB as DATE.
        $conflicting = RentalBooking::query()
            ->select('rental_unit_id')
            ->whereDate('start_date', '<', $end->copy()->addDays($buffer)->toDateString())
            ->whereDate('end_date', '>', $start->copy()->subDays($buffer)->toDateString());

        return RentalUnit::query()
            ->where('product_id', $product->getKey())
            ->where('status', 'available')
            ->whereNotIn('id', $conflicting)
            ->orderBy('code')
            ->get();
    }

    /**
     * Buffer from the default policy. Without a default policy the column
     * default (1 day) applies, so a missing policy never permits same-day
     * turnarounds.
     */
    private function bufferDays(): int
    {
        return (RentalPolicy::default() ?? new RentalPolicy)->buffer_days;
    }
}

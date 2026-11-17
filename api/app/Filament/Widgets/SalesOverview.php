<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\Rental;
use App\Models\RentalDeposit;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalesOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $todaySales = Order::query()
            ->where('payment_status', 'paid')
            ->whereDate('placed_at', today())
            ->sum('total_baisa');

        $openRentals = Rental::query()
            ->whereIn('status', ['confirmed', 'picked_up'])
            ->count();

        $outstandingDeposits = RentalDeposit::query()
            ->whereIn('status', ['held', 'charged'])
            ->sum('amount_baisa');

        $lowStock = ProductVariant::query()
            ->where('is_active', true)
            ->where('sale_stock', '<=', 2)
            ->count();

        return [
            Stat::make('Sales today', number_format($todaySales / 1000, 3).' OMR'),
            Stat::make('Open rentals', (string) $openRentals),
            Stat::make('Deposits outstanding', number_format($outstandingDeposits / 1000, 3).' OMR'),
            Stat::make('Low stock variants', (string) $lowStock),
        ];
    }
}

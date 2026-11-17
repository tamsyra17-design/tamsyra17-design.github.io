<?php

namespace App\Filament\Resources\RentalUnitResource\Actions;

use App\Models\RentalUnit;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Support\Icons\Heroicon;

/**
 * Takes a unit out for a date range (repairs, photo shoots, private use) by
 * recording a booking with no rental attached. Availability treats it like any
 * other booking, buffer days included.
 */
class BlockDatesAction
{
    public static function make(): Action
    {
        return Action::make('blockDates')
            ->label(__('admin.actions.block_dates'))
            ->icon(Heroicon::OutlinedNoSymbol)
            ->schema([
                DatePicker::make('start_date')
                    ->label(__('admin.fields.block_start_date'))
                    ->native(false)
                    ->required(),
                DatePicker::make('end_date')
                    ->label(__('admin.fields.block_end_date'))
                    ->helperText(__('admin.fields.block_end_date_help'))
                    ->native(false)
                    ->required()
                    ->after('start_date'),
            ])
            ->action(function (RentalUnit $record, array $data): void {
                $record->bookings()->create([
                    'start_date' => $data['start_date'],
                    'end_date' => $data['end_date'],
                    'rental_id' => null,
                ]);
            })
            ->successNotificationTitle(__('admin.actions.dates_blocked'));
    }
}

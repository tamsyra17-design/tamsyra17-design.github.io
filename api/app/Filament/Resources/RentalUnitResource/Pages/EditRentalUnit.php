<?php

namespace App\Filament\Resources\RentalUnitResource\Pages;

use App\Filament\Resources\RentalUnitResource;
use App\Filament\Resources\RentalUnitResource\Actions\BlockDatesAction;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRentalUnit extends EditRecord
{
    protected static string $resource = RentalUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            BlockDatesAction::make(),
            DeleteAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\RentalPolicyResource\Pages;

use App\Filament\Resources\RentalPolicyResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRentalPolicy extends EditRecord
{
    protected static string $resource = RentalPolicyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

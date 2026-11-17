<?php

namespace App\Filament\Resources\RentalPolicyResource\Pages;

use App\Filament\Resources\RentalPolicyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRentalPolicies extends ListRecords
{
    protected static string $resource = RentalPolicyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

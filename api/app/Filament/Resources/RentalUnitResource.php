<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RentalUnitResource\Pages\CreateRentalUnit;
use App\Filament\Resources\RentalUnitResource\Pages\EditRentalUnit;
use App\Filament\Resources\RentalUnitResource\Pages\ListRentalUnits;
use App\Filament\Resources\RentalUnitResource\Schemas\RentalUnitForm;
use App\Filament\Resources\RentalUnitResource\Tables\RentalUnitsTable;
use App\Models\RentalUnit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RentalUnitResource extends Resource
{
    protected static ?string $model = RentalUnit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'code';

    public static function getNavigationLabel(): string
    {
        return __('admin.resources.rental_units.label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.resources.rental_units.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.rental_units.label');
    }

    public static function form(Schema $schema): Schema
    {
        return RentalUnitForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RentalUnitsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRentalUnits::route('/'),
            'create' => CreateRentalUnit::route('/create'),
            'edit' => EditRentalUnit::route('/{record}/edit'),
        ];
    }
}

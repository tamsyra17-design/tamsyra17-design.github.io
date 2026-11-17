<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShippingZoneResource\Pages\CreateShippingZone;
use App\Filament\Resources\ShippingZoneResource\Pages\EditShippingZone;
use App\Filament\Resources\ShippingZoneResource\Pages\ListShippingZones;
use App\Filament\Resources\ShippingZoneResource\RelationManagers\RatesRelationManager;
use App\Filament\Resources\ShippingZoneResource\Schemas\ShippingZoneForm;
use App\Filament\Resources\ShippingZoneResource\Tables\ShippingZonesTable;
use App\Models\ShippingZone;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ShippingZoneResource extends Resource
{
    protected static ?string $model = ShippingZone::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?string $recordTitleAttribute = 'governorate';

    public static function getNavigationLabel(): string
    {
        return __('admin.resources.shipping_zones.label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.resources.shipping_zones.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.shipping_zones.label');
    }

    public static function form(Schema $schema): Schema
    {
        return ShippingZoneForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ShippingZonesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RatesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListShippingZones::route('/'),
            'create' => CreateShippingZone::route('/create'),
            'edit' => EditShippingZone::route('/{record}/edit'),
        ];
    }
}

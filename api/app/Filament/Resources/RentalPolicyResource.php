<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RentalPolicyResource\Pages\CreateRentalPolicy;
use App\Filament\Resources\RentalPolicyResource\Pages\EditRentalPolicy;
use App\Filament\Resources\RentalPolicyResource\Pages\ListRentalPolicies;
use App\Filament\Resources\RentalPolicyResource\Schemas\RentalPolicyForm;
use App\Filament\Resources\RentalPolicyResource\Tables\RentalPoliciesTable;
use App\Models\RentalPolicy;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RentalPolicyResource extends Resource
{
    protected static ?string $model = RentalPolicy::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationLabel(): string
    {
        return __('admin.resources.rental_policies.label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.resources.rental_policies.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.rental_policies.label');
    }

    public static function form(Schema $schema): Schema
    {
        return RentalPolicyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RentalPoliciesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRentalPolicies::route('/'),
            'create' => CreateRentalPolicy::route('/create'),
            'edit' => EditRentalPolicy::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources\ShippingZoneResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ShippingZonesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('governorate')
            ->columns([
                TextColumn::make('governorate')
                    ->label(__('admin.fields.governorate'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name.ar')
                    ->label(__('admin.fields.name_ar')),
                TextColumn::make('rates_count')
                    ->label(__('admin.relations.rates'))
                    ->counts('rates'),
                IconColumn::make('is_active')
                    ->label(__('admin.fields.is_active'))
                    ->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\RentalPolicyResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RentalPoliciesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('base_fee_baisa')
                    ->label(__('admin.fields.base_fee_baisa'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('extra_daily_baisa')
                    ->label(__('admin.fields.extra_daily_baisa'))
                    ->numeric(),
                TextColumn::make('deposit_baisa')
                    ->label(__('admin.fields.deposit_baisa'))
                    ->numeric(),
                TextColumn::make('deposit_mode')
                    ->label(__('admin.fields.deposit_mode'))
                    ->formatStateUsing(fn (string $state): string => __("admin.deposit_modes.{$state}"))
                    ->badge(),
                IconColumn::make('is_default')
                    ->label(__('admin.fields.is_default'))
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

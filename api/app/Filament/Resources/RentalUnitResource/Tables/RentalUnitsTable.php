<?php

namespace App\Filament\Resources\RentalUnitResource\Tables;

use App\Filament\Resources\RentalUnitResource\Actions\BlockDatesAction;
use App\Filament\Resources\RentalUnitResource\Schemas\RentalUnitForm;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RentalUnitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('product'))
            ->defaultSort('code')
            ->columns([
                TextColumn::make('code')
                    ->label(__('admin.fields.unit_code'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.sku')
                    ->label(__('admin.fields.sku'))
                    ->searchable(),
                TextColumn::make('product.name.ar')
                    ->label(__('admin.fields.product')),
                TextColumn::make('status')
                    ->label(__('admin.fields.status'))
                    ->formatStateUsing(fn (string $state): string => __("admin.unit_statuses.{$state}"))
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',
                        'maintenance' => 'warning',
                        default => 'gray',
                    })
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('admin.fields.status'))
                    ->options(RentalUnitForm::statusOptions()),
            ])
            ->recordActions([
                EditAction::make(),
                BlockDatesAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\ProductResource\Tables;

use App\Filament\Resources\ProductResource\Actions\DuplicateProductAction;
use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->when(
                Product::hasImagesRelation(),
                fn (Builder $query) => $query->with('images'),
            ))
            ->defaultSort('id', 'desc')
            ->columns([
                ImageColumn::make('primary_image')
                    ->label(__('admin.fields.primary_image'))
                    ->state(fn (Product $record): ?string => $record->primaryImagePath())
                    ->square(),
                TextColumn::make('name.ar')
                    ->label(__('admin.fields.name_ar'))
                    ->searchable(query: fn (Builder $query, string $search) => $query->search($search)),
                TextColumn::make('sku')
                    ->label(__('admin.fields.sku'))
                    ->searchable(),
                TextColumn::make('price_baisa')
                    ->label(__('admin.fields.price'))
                    ->formatStateUsing(fn (?int $state): string => $state === null
                        ? '—'
                        : number_format($state / 1000, 3).' OMR')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('admin.fields.is_active'))
                    ->boolean(),
                IconColumn::make('is_rentable')
                    ->label(__('admin.fields.is_rentable'))
                    ->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
                DuplicateProductAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

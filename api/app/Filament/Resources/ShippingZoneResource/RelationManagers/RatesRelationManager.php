<?php

namespace App\Filament\Resources\ShippingZoneResource\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class RatesRelationManager extends RelationManager
{
    protected static string $relationship = 'rates';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.relations.rates');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')
                    ->label(__('admin.fields.name'))
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                TextInput::make('min_weight_grams')
                    ->label(__('admin.fields.min_weight_grams'))
                    ->integer()
                    ->minValue(0)
                    ->maxValue(4294967295)
                    ->default(0)
                    ->required(),
                TextInput::make('max_weight_grams')
                    ->label(__('admin.fields.max_weight_grams'))
                    ->helperText(__('admin.fields.max_weight_help'))
                    ->integer()
                    ->maxValue(4294967295)
                    ->minValue(fn (Get $get): int => (int) $get('min_weight_grams')),
                TextInput::make('rate_baisa')
                    ->label(__('admin.fields.rate_baisa'))
                    ->helperText(__('admin.fields.baisa_help'))
                    ->integer()
                    ->minValue(0)
                    ->maxValue(4294967295)
                    ->required(),
                TextInput::make('free_over_baisa')
                    ->label(__('admin.fields.free_over_baisa'))
                    ->helperText(__('admin.fields.free_over_help'))
                    ->integer()
                    ->minValue(0)
                    ->maxValue(4294967295),
            ]);
    }

    public function table(Table $table): Table
    {
        $omr = fn (?int $state): string => $state === null ? '—' : number_format($state / 1000, 3).' OMR';

        return $table
            ->recordTitleAttribute('name')
            ->defaultSort('min_weight_grams')
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.fields.name')),
                TextColumn::make('min_weight_grams')
                    ->label(__('admin.fields.min_weight_grams'))
                    ->sortable(),
                TextColumn::make('max_weight_grams')
                    ->label(__('admin.fields.max_weight_grams'))
                    ->placeholder('∞'),
                TextColumn::make('rate_baisa')
                    ->label(__('admin.fields.rate'))
                    ->formatStateUsing($omr),
                TextColumn::make('free_over_baisa')
                    ->label(__('admin.fields.free_over_baisa'))
                    ->formatStateUsing($omr)
                    ->placeholder('—'),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

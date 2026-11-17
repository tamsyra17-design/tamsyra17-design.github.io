<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.relations.variants');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('sku')
                    ->label(__('admin.fields.sku'))
                    ->required()
                    ->maxLength(64)
                    ->unique(ignoreRecord: true),
                Toggle::make('is_active')
                    ->label(__('admin.fields.is_active'))
                    ->default(true)
                    ->inline(false),
                Fieldset::make(__('admin.fields.name'))
                    ->schema([
                        TextInput::make('name.ar')
                            ->label(__('admin.fields.arabic'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('name.en')
                            ->label(__('admin.fields.english'))
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columnSpanFull(),
                TextInput::make('price_baisa')
                    ->label(__('admin.fields.price_baisa'))
                    ->helperText(__('admin.fields.variant_price_help'))
                    ->integer()
                    ->minValue(0)
                    ->maxValue(4294967295),
                TextInput::make('sale_stock')
                    ->label(__('admin.fields.sale_stock'))
                    ->integer()
                    ->minValue(0)
                    ->maxValue(4294967295)
                    ->default(0)
                    ->required(),
                KeyValue::make('attributes')
                    ->label(__('admin.fields.attributes'))
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('sku')
            ->columns([
                TextColumn::make('sku')
                    ->label(__('admin.fields.sku'))
                    ->searchable(),
                TextColumn::make('name.ar')
                    ->label(__('admin.fields.name_ar')),
                TextColumn::make('name.en')
                    ->label(__('admin.fields.name_en')),
                TextColumn::make('price_baisa')
                    ->label(__('admin.fields.price'))
                    ->formatStateUsing(fn (?int $state): string => $state === null
                        ? '—'
                        : number_format($state / 1000, 3).' OMR'),
                TextColumn::make('sale_stock')
                    ->label(__('admin.fields.sale_stock'))
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('admin.fields.is_active'))
                    ->boolean(),
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

<?php

namespace App\Filament\Resources\ProductResource\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make(__('admin.sections.basic'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('sku')
                            ->label(__('admin.fields.sku'))
                            ->required()
                            ->maxLength(64)
                            ->unique(ignoreRecord: true),
                        TextInput::make('slug')
                            ->label(__('admin.fields.slug'))
                            ->required()
                            ->maxLength(255)
                            ->alphaDash()
                            ->unique(ignoreRecord: true),
                        Select::make('category_id')
                            ->label(__('admin.fields.category'))
                            ->relationship('category', 'slug')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull(),
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
                        Fieldset::make(__('admin.fields.short_description'))
                            ->schema([
                                Textarea::make('short_description.ar')
                                    ->label(__('admin.fields.arabic'))
                                    ->rows(2)
                                    ->maxLength(500),
                                Textarea::make('short_description.en')
                                    ->label(__('admin.fields.english'))
                                    ->rows(2)
                                    ->maxLength(500),
                            ])
                            ->columnSpanFull(),
                        Fieldset::make(__('admin.fields.description'))
                            ->schema([
                                Textarea::make('description.ar')
                                    ->label(__('admin.fields.arabic'))
                                    ->rows(5),
                                Textarea::make('description.en')
                                    ->label(__('admin.fields.english'))
                                    ->rows(5),
                            ])
                            ->columnSpanFull(),
                    ]),
                Section::make(__('admin.sections.pricing'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('price_baisa')
                            ->label(__('admin.fields.price_baisa'))
                            ->helperText(__('admin.fields.price_help'))
                            ->integer()
                            ->minValue(0)
                            ->maxValue(4294967295),
                        TextInput::make('weight_grams')
                            ->label(__('admin.fields.weight_grams'))
                            ->integer()
                            ->minValue(0)
                            ->maxValue(4294967295),
                    ]),
                Section::make(__('admin.sections.flags'))
                    ->columns(3)
                    ->schema([
                        Toggle::make('is_active')
                            ->label(__('admin.fields.is_active'))
                            ->default(true),
                        Toggle::make('is_rentable')
                            ->label(__('admin.fields.is_rentable'))
                            ->default(false),
                        Toggle::make('is_featured')
                            ->label(__('admin.fields.is_featured'))
                            ->default(false),
                    ]),
            ]);
    }
}

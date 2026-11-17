<?php

namespace App\Filament\Resources\ShippingZoneResource\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;

class ShippingZoneForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('governorate')
                    ->label(__('admin.fields.governorate'))
                    ->helperText(__('admin.fields.governorate_help'))
                    ->required()
                    ->maxLength(255)
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
            ]);
    }
}

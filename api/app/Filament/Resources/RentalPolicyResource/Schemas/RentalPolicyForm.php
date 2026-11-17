<?php

namespace App\Filament\Resources\RentalPolicyResource\Schemas;

use App\Models\RentalPolicy;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class RentalPolicyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.sections.basic'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('admin.fields.name'))
                            ->required()
                            ->maxLength(255),
                        Toggle::make('is_default')
                            ->label(__('admin.fields.is_default'))
                            ->helperText(__('admin.fields.is_default_help'))
                            ->inline(false),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make(__('admin.sections.pricing'))
                    ->schema([
                        self::days('base_days')->default(1)->minValue(1),
                        self::baisa('base_fee_baisa')->required(),
                        self::baisa('extra_daily_baisa')->required(),
                        self::baisa('weekly_fee_baisa')
                            ->helperText(__('admin.fields.weekly_fee_help')),
                        self::baisa('late_fee_per_day_baisa')->required()->default(0),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make(__('admin.sections.rental_period'))
                    ->schema([
                        self::days('min_days')->default(1)->minValue(1),
                        self::days('max_days')
                            ->default(30)
                            ->minValue(fn (Get $get): int => max(1, (int) $get('min_days'))),
                        self::days('buffer_days')
                            ->default(1)
                            ->helperText(__('admin.fields.buffer_days_help')),
                        self::days('inspection_days')
                            ->default(2)
                            ->helperText(__('admin.fields.inspection_days_help')),
                        Toggle::make('return_day_inclusive')
                            ->label(__('admin.fields.return_day_inclusive'))
                            ->helperText(__('admin.fields.return_day_inclusive_help'))
                            ->inline(false),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make(__('admin.sections.deposit'))
                    ->schema([
                        self::baisa('deposit_baisa')->required()->default(0),
                        Select::make('deposit_mode')
                            ->label(__('admin.fields.deposit_mode'))
                            ->options(collect(RentalPolicy::DEPOSIT_MODES)
                                ->mapWithKeys(fn (string $mode) => [$mode => __("admin.deposit_modes.{$mode}")])
                                ->all())
                            ->default('charge')
                            ->required(),
                        Toggle::make('require_id')
                            ->label(__('admin.fields.require_id'))
                            ->inline(false),
                        Toggle::make('require_contract')
                            ->label(__('admin.fields.require_contract'))
                            ->inline(false),
                        Textarea::make('damage_fee_notes')
                            ->label(__('admin.fields.damage_fee_notes'))
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    private static function baisa(string $field): TextInput
    {
        return TextInput::make($field)
            ->label(__("admin.fields.{$field}"))
            ->helperText(__('admin.fields.baisa_help'))
            ->integer()
            ->minValue(0);
    }

    private static function days(string $field): TextInput
    {
        return TextInput::make($field)
            ->label(__("admin.fields.{$field}"))
            ->integer()
            ->minValue(0)
            ->required();
    }
}

<?php

namespace App\Filament\Resources\SettingResource\Schemas;

use App\Models\Setting;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\CodeEditor\Enums\Language;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')
                    ->label(__('admin.fields.key'))
                    ->required()
                    ->maxLength(255)
                    ->alphaDash()
                    ->unique(ignoreRecord: true),
                Select::make('group')
                    ->label(__('admin.fields.group'))
                    ->options(self::groupOptions())
                    ->default('general')
                    ->required()
                    ->helperText(__('admin.fields.group_help')),
                // Values are free-form JSON: "text", 123, {"ar": "…", "en": "…"} or ["cod"].
                CodeEditor::make('value')
                    ->label(__('admin.fields.value'))
                    ->language(Language::Json)
                    ->helperText(__('admin.fields.value_help'))
                    ->formatStateUsing(fn (mixed $state): string => json_encode(
                        $state,
                        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
                    ))
                    ->dehydrateStateUsing(fn (?string $state): mixed => json_decode($state ?? 'null', true))
                    ->rule('json')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    /**
     * @return array<string, string>
     */
    public static function groupOptions(): array
    {
        return collect(Setting::GROUPS)
            ->mapWithKeys(fn (string $group): array => [$group => __("admin.setting_groups.{$group}")])
            ->all();
    }
}

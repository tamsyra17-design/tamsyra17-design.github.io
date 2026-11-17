<?php

namespace App\Filament\Resources\SettingResource\Tables;

use App\Filament\Resources\SettingResource\Schemas\SettingForm;
use App\Models\Setting;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class SettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('key')
            ->columns([
                TextColumn::make('key')
                    ->label(__('admin.fields.key'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('group')
                    ->label(__('admin.fields.group'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => SettingForm::groupOptions()[$state] ?? $state),
                TextColumn::make('value')
                    ->label(__('admin.fields.value'))
                    // Secrets are never echoed back in the list.
                    ->getStateUsing(fn (Setting $record): string => $record->group === Setting::PRIVATE_GROUP
                        ? '••••••'
                        : Str::limit((string) json_encode($record->value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 80)),
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

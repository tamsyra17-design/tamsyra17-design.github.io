<?php

namespace App\Filament\Resources\UserResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.fields.name'))
                    ->searchable(),
                TextColumn::make('email')
                    ->label(__('admin.fields.email'))
                    ->searchable(),
                TextColumn::make('phone')
                    ->label(__('admin.fields.phone'))
                    ->searchable(),
                TextColumn::make('roles.name')
                    ->label(__('admin.fields.roles'))
                    ->badge()
                    ->separator(','),
                TextColumn::make('email_verified_at')
                    ->label(__('admin.fields.email_verified_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('admin.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('admin.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        // Keep the policy's self-delete guard effective here too.
                        ->authorizeIndividualRecords('delete'),
                ]),
            ]);
    }
}

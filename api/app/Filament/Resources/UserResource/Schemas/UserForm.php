<?php

namespace App\Filament\Resources\UserResource\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('admin.fields.name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label(__('admin.fields.email'))
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('phone')
                    ->label(__('admin.fields.phone'))
                    ->tel()
                    ->maxLength(32)
                    ->unique(ignoreRecord: true),
                TextInput::make('password')
                    ->label(__('admin.fields.password'))
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->minLength(8)
                    ->maxLength(255),
                Select::make('roles')
                    ->label(__('admin.fields.roles'))
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),
            ]);
    }
}

<?php

namespace App\Filament\Resources\UserResource\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label(__('admin.fields.name')),
                TextEntry::make('email')
                    ->label(__('admin.fields.email')),
                TextEntry::make('phone')
                    ->label(__('admin.fields.phone'))
                    ->placeholder('-'),
                TextEntry::make('roles.name')
                    ->label(__('admin.fields.roles'))
                    ->badge()
                    ->placeholder('-'),
                TextEntry::make('email_verified_at')
                    ->label(__('admin.fields.email_verified_at'))
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->label(__('admin.fields.created_at'))
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label(__('admin.fields.updated_at'))
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}

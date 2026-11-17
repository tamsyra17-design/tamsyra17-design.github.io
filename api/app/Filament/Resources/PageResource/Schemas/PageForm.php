<?php

namespace App\Filament\Resources\PageResource\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('slug')
                    ->label(__('admin.fields.slug'))
                    ->required()
                    ->maxLength(255)
                    ->alphaDash()
                    ->unique(ignoreRecord: true),
                Toggle::make('is_published')
                    ->label(__('admin.fields.is_published'))
                    ->default(false)
                    ->inline(false),
                Tabs::make('translations')
                    ->tabs([
                        self::languageTab('ar', __('admin.fields.arabic')),
                        self::languageTab('en', __('admin.fields.english')),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    private static function languageTab(string $locale, string $label): Tab
    {
        return Tab::make($label)
            ->schema([
                TextInput::make("title.{$locale}")
                    ->label(__('admin.fields.title'))
                    ->required()
                    ->maxLength(255),
                RichEditor::make("body.{$locale}")
                    ->label(__('admin.fields.body')),
            ]);
    }
}

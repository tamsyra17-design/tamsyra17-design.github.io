<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.relations.images');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                FileUpload::make('path')
                    ->label(__('admin.fields.image'))
                    ->image()
                    ->directory(fn (): string => 'products/'.$this->getOwnerRecord()->getKey())
                    ->required(),
                Fieldset::make(__('admin.fields.alt'))
                    ->schema([
                        TextInput::make('alt.ar')
                            ->label(__('admin.fields.arabic'))
                            ->maxLength(255),
                        TextInput::make('alt.en')
                            ->label(__('admin.fields.english'))
                            ->maxLength(255),
                    ]),
                TextInput::make('sort_order')
                    ->label(__('admin.fields.sort_order'))
                    ->integer()
                    ->minValue(0)
                    ->default(0)
                    ->required(),
                Toggle::make('is_primary')
                    ->label(__('admin.fields.is_primary'))
                    ->default(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('path')
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                ImageColumn::make('path')
                    ->label(__('admin.fields.image'))
                    ->square(),
                TextColumn::make('alt.ar')
                    ->label(__('admin.fields.alt_ar')),
                TextColumn::make('alt.en')
                    ->label(__('admin.fields.alt_en')),
                IconColumn::make('is_primary')
                    ->label(__('admin.fields.is_primary'))
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label(__('admin.fields.sort_order'))
                    ->sortable(),
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

<?php

namespace App\Filament\Resources\CategoryResource\Schemas;

use App\Models\Category;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class CategoryForm
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
                Select::make('parent_id')
                    ->label(__('admin.fields.parent'))
                    ->relationship(
                        'parent',
                        'slug',
                        // A category cannot be its own parent.
                        fn (Builder $query, ?Category $record) => $query->when(
                            $record,
                            fn (Builder $query) => $query->whereKeyNot($record->getKey()),
                        ),
                    )
                    ->searchable()
                    ->preload(),
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
                    ]),
                Fieldset::make(__('admin.fields.description'))
                    ->schema([
                        Textarea::make('description.ar')
                            ->label(__('admin.fields.arabic'))
                            ->rows(3),
                        Textarea::make('description.en')
                            ->label(__('admin.fields.english'))
                            ->rows(3),
                    ]),
                FileUpload::make('image_path')
                    ->label(__('admin.fields.image'))
                    ->image()
                    ->directory('categories'),
                TextInput::make('sort_order')
                    ->label(__('admin.fields.sort_order'))
                    ->integer()
                    ->minValue(0)
                    ->default(0)
                    ->required(),
                Toggle::make('is_active')
                    ->label(__('admin.fields.is_active'))
                    ->default(true),
                Toggle::make('is_rental_only')
                    ->label(__('admin.fields.is_rental_only'))
                    ->default(false),
            ]);
    }
}

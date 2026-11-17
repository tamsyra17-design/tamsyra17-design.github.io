<?php

namespace App\Filament\Resources\RentalUnitResource\Schemas;

use App\Models\Product;
use App\Models\RentalUnit;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Unique;

class RentalUnitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.sections.basic'))
                    ->schema([
                        Select::make('product_id')
                            ->label(__('admin.fields.product'))
                            ->relationship(
                                'product',
                                'sku',
                                modifyQueryUsing: fn (Builder $query) => $query->where('is_rentable', true),
                            )
                            ->getOptionLabelFromRecordUsing(fn (Product $record): string => self::productLabel($record))
                            ->searchable(['sku', 'slug'])
                            ->preload()
                            ->live()
                            ->required(),
                        TextInput::make('code')
                            ->label(__('admin.fields.unit_code'))
                            ->helperText(__('admin.fields.unit_code_help'))
                            ->required()
                            ->maxLength(255)
                            ->unique(
                                ignoreRecord: true,
                                modifyRuleUsing: fn (Unique $rule, Get $get) => $rule->where('product_id', $get('product_id')),
                            ),
                        Select::make('status')
                            ->label(__('admin.fields.status'))
                            ->options(self::statusOptions())
                            ->default('available')
                            ->required(),
                        Textarea::make('notes')
                            ->label(__('admin.fields.notes'))
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    /**
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        return collect(RentalUnit::STATUSES)
            ->mapWithKeys(fn (string $status) => [$status => __("admin.unit_statuses.{$status}")])
            ->all();
    }

    private static function productLabel(Product $product): string
    {
        $name = $product->name['ar'] ?? $product->name['en'] ?? null;

        return is_string($name) && $name !== '' ? "{$product->sku} — {$name}" : $product->sku;
    }
}

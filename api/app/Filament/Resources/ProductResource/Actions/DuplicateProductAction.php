<?php

namespace App\Filament\Resources\ProductResource\Actions;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use Filament\Actions\ReplicateAction;
use Illuminate\Support\Str;

/**
 * Clones a product's own attributes only. Relations (images, and later
 * variants) are not copied. The copy gets a fresh sku/slug and starts as an
 * inactive, unfeatured draft so it never goes live by accident.
 */
class DuplicateProductAction
{
    public static function make(): ReplicateAction
    {
        return ReplicateAction::make('duplicate')
            ->label(__('admin.actions.duplicate'))
            ->requiresConfirmation()
            ->beforeReplicaSaved(function (Product $replica): void {
                $suffix = Str::lower(Str::random(5));

                $replica->sku = Str::limit($replica->sku, 52, '').'-COPY-'.Str::upper($suffix);
                $replica->slug = Str::limit($replica->slug, 240, '').'-copy-'.$suffix;
                $replica->is_active = false;
                $replica->is_featured = false;
            })
            ->successNotificationTitle(__('admin.actions.duplicated'))
            ->successRedirectUrl(fn (Product $replica): string => ProductResource::getUrl('edit', ['record' => $replica]));
    }
}

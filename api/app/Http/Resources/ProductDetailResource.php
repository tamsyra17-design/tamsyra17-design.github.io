<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Detail shape: full translations, category, images, variants and
 * sale/rental availability.
 *
 * @mixin Product
 */
class ProductDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'name' => ProductResource::translations($this->name),
            'short_description' => ProductResource::translations($this->short_description),
            'description' => ProductResource::translations($this->description),
            'price_baisa' => $this->price_baisa,
            'weight_grams' => $this->weight_grams,
            'is_rentable' => $this->is_rentable,
            'is_featured' => $this->is_featured,
            'available_for_sale' => $this->isAvailableForSale(),
            'available_for_rental' => $this->isAvailableForRental(),
            'model_3d_url' => ProductResource::publicUrl($this->model_3d_url),
            'model_3d_poster' => ProductResource::publicUrl($this->model_3d_poster),
            'category' => $this->category ? new CategoryResource($this->category) : null,
            'images' => $this->imagesPayload(),
            'variants' => $this->variantsPayload(),
        ];
    }

    /**
     * ProductImage lands in Task 8; until then the list is always empty.
     *
     * @return list<array<string, mixed>>
     */
    private function imagesPayload(): array
    {
        if (! Product::hasImagesRelation()) {
            return [];
        }

        return $this->images->map(fn ($image) => [
            'id' => $image->id,
            'path' => $image->path,
            'alt' => ProductResource::translations($image->alt),
            'is_primary' => (bool) $image->is_primary,
        ])->values()->all();
    }

    /**
     * ProductVariant lands in Task 8; until then the list is always empty.
     *
     * @return list<array<string, mixed>>
     */
    private function variantsPayload(): array
    {
        if (! Product::hasVariantsRelation()) {
            return [];
        }

        return $this->variants->where('is_active', true)->map(fn ($variant) => [
            'id' => $variant->id,
            'sku' => $variant->sku,
            'name' => ProductResource::translations($variant->name),
            'price_baisa' => $variant->price_baisa,
            'attributes' => $variant->attributes ?? [],
        ])->values()->all();
    }
}

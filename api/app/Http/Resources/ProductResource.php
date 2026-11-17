<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * List shape: enough for a product card.
 *
 * @mixin Product
 */
class ProductResource extends JsonResource
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
            'name' => self::translations($this->name),
            'short_description' => self::translations($this->short_description),
            'price_baisa' => $this->price_baisa,
            'category_id' => $this->category_id,
            'is_rentable' => $this->is_rentable,
            'is_featured' => $this->is_featured,
            'available_for_sale' => $this->isAvailableForSale(),
            'available_for_rental' => $this->isAvailableForRental(),
        ];
    }

    /**
     * Always emit an {ar, en} pair so clients get a stable shape.
     *
     * @return array{ar: ?string, en: ?string}
     */
    public static function translations(mixed $value): array
    {
        $value = is_array($value) ? $value : [];

        return [
            'ar' => $value['ar'] ?? null,
            'en' => $value['en'] ?? null,
        ];
    }
}

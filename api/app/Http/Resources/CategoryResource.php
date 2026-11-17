<?php

namespace App\Http\Resources;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Category
 */
class CategoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => [
                'ar' => $this->name['ar'] ?? null,
                'en' => $this->name['en'] ?? null,
            ],
            'parent_id' => $this->parent_id,
            'is_rental_only' => $this->is_rental_only,
        ];
    }
}

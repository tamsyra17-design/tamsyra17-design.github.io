<?php

namespace App\Models;

use App\Observers\ProductObserver;
use App\Support\ArabicNormalizer;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    /**
     * search_text is derived (see booted()) and deliberately not fillable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'category_id',
        'sku',
        'slug',
        'name',
        'short_description',
        'description',
        'price_baisa',
        'is_active',
        'is_rentable',
        'is_featured',
        'weight_grams',
        'model_3d_url',
        'model_3d_poster',
    ];

    /**
     * Model-level defaults mirror the column defaults.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_active' => true,
        'is_rentable' => false,
        'is_featured' => false,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'name' => 'array',
            'short_description' => 'array',
            'description' => 'array',
            'price_baisa' => 'integer',
            'weight_grams' => 'integer',
            'is_active' => 'boolean',
            'is_rentable' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::observe(ProductObserver::class);
    }

    /**
     * Normalized, lower-cased name.ar + name.en + sku + description.ar.
     */
    public function buildSearchText(): string
    {
        $localized = fn (mixed $value, string $locale): ?string => is_array($value) && is_string($value[$locale] ?? null)
            ? $value[$locale]
            : null;

        $parts = array_filter([
            $localized($this->name, 'ar'),
            $localized($this->name, 'en'),
            $this->sku,
            $localized($this->description, 'ar'),
        ], fn (?string $part): bool => $part !== null && trim($part) !== '');

        return static::normalizeForSearch(implode(' ', $parts));
    }

    /**
     * The folding applied to both search_text and incoming search terms.
     */
    public static function normalizeForSearch(string $text): string
    {
        return Str::lower(trim(ArabicNormalizer::normalize($text)));
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasMany<ProductImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @return HasMany<ProductVariant, $this>
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * @return HasMany<RentalUnit, $this>
     */
    public function rentalUnits(): HasMany
    {
        return $this->hasMany(RentalUnit::class);
    }

    /**
     * Units available for sale, summed over active variants. Uses the loaded
     * relation when present to avoid an extra query.
     */
    public function saleStock(): int
    {
        if ($this->relationLoaded('variants')) {
            return (int) $this->variants->where('is_active', true)->sum('sale_stock');
        }

        return (int) $this->variants()->where('is_active', true)->sum('sale_stock');
    }

    public function inStock(): bool
    {
        return $this->saleStock() > 0;
    }

    public static function hasImagesRelation(): bool
    {
        return class_exists(ProductImage::class);
    }

    public static function hasVariantsRelation(): bool
    {
        return class_exists(ProductVariant::class);
    }

    /**
     * Path of the primary image (or the first image), or null when none.
     */
    public function primaryImagePath(): ?string
    {
        if (! static::hasImagesRelation()) {
            return null;
        }

        $image = $this->images->firstWhere('is_primary', true) ?? $this->images->first();

        return $image?->path;
    }

    /**
     * Active and priced. Stock checks are added with variants (Task 8).
     */
    public function isAvailableForSale(): bool
    {
        return $this->is_active && $this->price_baisa !== null;
    }

    public function isAvailableForRental(): bool
    {
        return $this->is_active && $this->is_rentable;
    }

    /**
     * @param  Builder<Product>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Case-insensitive, Arabic-tolerant substring search over search_text,
     * with LIKE wildcards in the term escaped so they match literally.
     *
     * @param  Builder<Product>  $query
     */
    public function scopeSearch(Builder $query, string $term): void
    {
        $term = static::normalizeForSearch($term);

        if ($term === '') {
            return;
        }

        $escaped = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term);

        $query->whereRaw("search_text like ? escape '!'", ['%'.$escaped.'%']);
    }
}

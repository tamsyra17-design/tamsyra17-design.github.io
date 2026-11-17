<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductDetailResource;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    private const PER_PAGE = 24;

    /**
     * Paginated active products, filterable by category slug, search term
     * and rentability.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $category = $request->query('category');
        $term = $request->query('q');

        $products = Product::query()
            ->active()
            ->when(is_string($category) && $category !== '', fn (Builder $query) => $query->whereHas(
                'category',
                fn (Builder $query) => $query->where('slug', $category),
            ))
            ->when(is_string($term), fn (Builder $query) => $query->search($term))
            ->when($request->filled('rentable'), fn (Builder $query) => $query->where(
                'is_rentable',
                $request->boolean('rentable'),
            ))
            ->orderByDesc('is_featured')
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return ProductResource::collection($products);
    }

    /**
     * A single active product by slug.
     */
    public function show(string $slug): ProductDetailResource
    {
        $product = Product::query()
            ->active()
            ->where('slug', $slug)
            ->with('category')
            ->firstOrFail();

        if (Product::hasImagesRelation()) {
            $product->load('images');
        }

        if (Product::hasVariantsRelation()) {
            $product->load('variants');
        }

        return new ProductDetailResource($product);
    }
}

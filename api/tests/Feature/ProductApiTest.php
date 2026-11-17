<?php

// api/tests/Feature/ProductApiTest.php
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists products and filters by category', function () {
    $cat = Category::factory()->create();
    Product::factory()->create(['category_id' => $cat->id, 'price_baisa' => 12500]);
    Product::factory()->create();

    $this->getJson('/api/v1/products?category='.$cat->slug)
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.price_baisa', 12500);
});

it('shows a product by slug', function () {
    $p = Product::factory()->create(['slug' => 'khanjar-1']);
    $this->getJson('/api/v1/products/khanjar-1')->assertOk()->assertJsonPath('data.slug', 'khanjar-1');
});

it('hides inactive products from list and detail', function () {
    Product::factory()->inactive()->create(['slug' => 'hidden']);

    $this->getJson('/api/v1/products')->assertOk()->assertJsonCount(0, 'data');
    $this->getJson('/api/v1/products/hidden')->assertNotFound();
});

it('filters by rentable flag', function () {
    Product::factory()->rentable()->create();
    Product::factory()->create();

    $this->getJson('/api/v1/products?rentable=1')->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.is_rentable', true);
    $this->getJson('/api/v1/products?rentable=0')->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.is_rentable', false);
});

it('searches names in either language and sku', function () {
    Product::factory()->create(['sku' => 'KH-001', 'name' => ['ar' => 'خنجر فضي', 'en' => 'Silver Khanjar']]);
    Product::factory()->create(['name' => ['ar' => 'مصر', 'en' => 'Massar 50% off']]);

    $this->getJson('/api/v1/products?q=khanjar')->assertJsonCount(1, 'data');
    $this->getJson('/api/v1/products?q='.urlencode('خنجر'))->assertJsonCount(1, 'data');
    $this->getJson('/api/v1/products?q=kh-001')->assertJsonCount(1, 'data');
    $this->getJson('/api/v1/products?q='.urlencode('%'))->assertJsonCount(1, 'data');
});

it('paginates the listing', function () {
    Product::factory()->count(30)->create();

    $this->getJson('/api/v1/products?page=2')
        ->assertOk()
        ->assertJsonCount(6, 'data')
        ->assertJsonPath('meta.current_page', 2)
        ->assertJsonPath('meta.total', 30);
});

it('returns detail with category, availability flags and media lists', function () {
    $cat = Category::factory()->create(['slug' => 'rentals']);
    Product::factory()->rentalOnly()->create(['slug' => 'wedding-set', 'category_id' => $cat->id]);

    $this->getJson('/api/v1/products/wedding-set')
        ->assertOk()
        ->assertJsonPath('data.category.slug', 'rentals')
        ->assertJsonPath('data.price_baisa', null)
        ->assertJsonPath('data.available_for_sale', false)
        ->assertJsonPath('data.available_for_rental', true)
        ->assertJsonPath('data.images', [])
        ->assertJsonPath('data.variants', []);
});

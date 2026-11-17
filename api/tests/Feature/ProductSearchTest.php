<?php

// api/tests/Feature/ProductSearchTest.php
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('finds a product by normalized Arabic query', function () {
    Product::factory()->create(['name' => ['ar' => 'خِنْجَر فاخر', 'en' => 'Luxury Khanjar']]);
    $this->getJson('/api/v1/products?q=خنجر')->assertOk()->assertJsonCount(1, 'data');
});

it('folds hamza, ta marbuta and diacritics on both sides', function () {
    Product::factory()->create(['name' => ['ar' => 'مُصَرَّة أصيلة', 'en' => 'Massar']]);

    $this->getJson('/api/v1/products?q='.urlencode('مصره'))->assertJsonCount(1, 'data');
    $this->getJson('/api/v1/products?q='.urlencode('اصيله'))->assertJsonCount(1, 'data');
    $this->getJson('/api/v1/products?q='.urlencode('مُصَرَّة'))->assertJsonCount(1, 'data');
});

it('searches the arabic description', function () {
    Product::factory()->create(['description' => ['ar' => 'مصنوع من الفـضـة', 'en' => 'Made of silver']]);

    $this->getJson('/api/v1/products?q='.urlencode('فضه'))->assertJsonCount(1, 'data');
});

it('combines search with category and rentable filters', function () {
    $category = Category::factory()->create(['slug' => 'khanjars']);
    Product::factory()->for($category)->rentable()->create(['name' => ['ar' => 'خنجر للإيجار', 'en' => 'Rental Khanjar']]);
    Product::factory()->for($category)->create(['name' => ['ar' => 'خنجر للبيع', 'en' => 'Sale Khanjar']]);
    Product::factory()->create(['name' => ['ar' => 'خنجر آخر', 'en' => 'Other Khanjar']]);

    $this->getJson('/api/v1/products?q='.urlencode('خنجر'))->assertJsonCount(3, 'data');
    $this->getJson('/api/v1/products?q='.urlencode('خنجر').'&category=khanjars')->assertJsonCount(2, 'data');
    $this->getJson('/api/v1/products?q='.urlencode('خنجر').'&category=khanjars&rentable=1')->assertJsonCount(1, 'data');
    $this->getJson('/api/v1/products?q='.urlencode('خنجر').'&rentable=0')->assertJsonCount(2, 'data');
});

<?php

use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Filament\Resources\ProductResource\RelationManagers\ImagesRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\VariantsRelationManager;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

// api/tests/Feature/ProductVariantTest.php
it('reports sale stock across variants', function () {
    $p = Product::factory()->create();
    ProductVariant::factory()->create(['product_id' => $p->id, 'sale_stock' => 3]);
    ProductVariant::factory()->create(['product_id' => $p->id, 'sale_stock' => 2]);
    expect($p->fresh()->saleStock())->toBe(5);
    expect($p->fresh()->inStock())->toBeTrue();
});

it('ignores inactive variants and reports no stock without variants', function () {
    $p = Product::factory()->create();
    expect($p->saleStock())->toBe(0)->and($p->inStock())->toBeFalse();

    ProductVariant::factory()->inactive()->create(['product_id' => $p->id, 'sale_stock' => 7]);
    ProductVariant::factory()->create(['product_id' => $p->id, 'sale_stock' => 0]);

    expect($p->fresh()->saleStock())->toBe(0)
        ->and($p->fresh()->inStock())->toBeFalse()
        ->and($p->fresh()->load('variants')->saleStock())->toBe(0);
});

it('keeps a single primary image per product', function () {
    $p = Product::factory()->create();
    $first = ProductImage::factory()->primary()->create(['product_id' => $p->id]);
    $second = ProductImage::factory()->primary()->create(['product_id' => $p->id]);

    expect($first->fresh()->is_primary)->toBeFalse()
        ->and($second->fresh()->is_primary)->toBeTrue()
        ->and($p->fresh()->primaryImagePath())->toBe($second->path);
});

it('exposes images and active variants on the product detail api', function () {
    $p = Product::factory()->create();
    ProductImage::factory()->primary()->create(['product_id' => $p->id, 'path' => 'products/x.jpg']);
    ProductVariant::factory()->create(['product_id' => $p->id, 'sku' => 'V-1', 'attributes' => ['size' => 'M']]);
    ProductVariant::factory()->inactive()->create(['product_id' => $p->id, 'sku' => 'V-2']);

    $this->getJson("/api/v1/products/{$p->slug}")
        ->assertOk()
        ->assertJsonPath('data.images.0.path', 'products/x.jpg')
        ->assertJsonCount(1, 'data.variants')
        ->assertJsonPath('data.variants.0.sku', 'V-1')
        ->assertJsonPath('data.variants.0.attributes', ['size' => 'M']);
});

it('renders the image and variant relation managers', function () {
    $this->seed(RoleSeeder::class);
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    $p = Product::factory()->create();
    ProductVariant::factory()->create(['product_id' => $p->id, 'sku' => 'VAR-SHOWN']);

    $this->actingAs($admin);

    Livewire::test(VariantsRelationManager::class, [
        'ownerRecord' => $p,
        'pageClass' => EditProduct::class,
    ])->assertOk()->assertSee('VAR-SHOWN');

    Livewire::test(ImagesRelationManager::class, [
        'ownerRecord' => $p,
        'pageClass' => EditProduct::class,
    ])->assertOk();
});

it('creates a variant from the relation manager form', function () {
    $this->seed(RoleSeeder::class);
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    $p = Product::factory()->create();

    $this->actingAs($admin);

    Livewire::test(VariantsRelationManager::class, [
        'ownerRecord' => $p,
        'pageClass' => EditProduct::class,
    ])
        ->callAction(TestAction::make('create')->table(), [
            'sku' => 'KH-100-M',
            'name' => ['ar' => 'متوسط', 'en' => 'Medium'],
            'price_baisa' => 45000,
            'sale_stock' => 4,
            'attributes' => ['size' => 'M'],
            'is_active' => true,
        ])
        ->assertHasNoFormErrors();

    $variant = ProductVariant::where('sku', 'KH-100-M')->firstOrFail();

    expect($variant->product_id)->toBe($p->id)
        ->and($variant->attributes)->toBe(['size' => 'M'])
        ->and($variant->name)->toBe(['ar' => 'متوسط', 'en' => 'Medium'])
        ->and($p->fresh()->saleStock())->toBe(4);
});

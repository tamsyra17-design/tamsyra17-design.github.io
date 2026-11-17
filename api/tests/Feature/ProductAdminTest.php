<?php

use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('super_admin');
});

it('renders the product admin pages', function () {
    $product = Product::factory()->create(['sku' => 'KH-777', 'price_baisa' => 12500]);

    $this->actingAs($this->admin)->get('/admin/products')
        ->assertOk()
        ->assertSee('KH-777')
        ->assertSee('12.500 OMR');
    $this->actingAs($this->admin)->get('/admin/products/create')->assertOk();
    $this->actingAs($this->admin)->get("/admin/products/{$product->id}/edit")->assertOk();
});

it('creates a product from the admin form', function () {
    $category = Category::factory()->create();

    $this->actingAs($this->admin);

    Livewire::test(CreateProduct::class)
        ->fillForm([
            'sku' => 'KH-100',
            'slug' => 'silver-khanjar',
            'category_id' => $category->id,
            'name' => ['ar' => 'خنجر فضي', 'en' => 'Silver Khanjar'],
            'price_baisa' => 45000,
            'weight_grams' => 900,
            'is_rentable' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $product = Product::where('sku', 'KH-100')->firstOrFail();

    expect($product->name)->toBe(['ar' => 'خنجر فضي', 'en' => 'Silver Khanjar'])
        ->and($product->price_baisa)->toBe(45000)
        ->and($product->is_rentable)->toBeTrue()
        ->and($product->search_text)->toContain('silver khanjar');
});

it('duplicates a product as an inactive draft with a new sku and slug', function () {
    $original = Product::factory()->featured()->create(['sku' => 'KH-1', 'slug' => 'khanjar-1']);

    $this->actingAs($this->admin);

    Livewire::test(ListProducts::class)
        ->callAction(TestAction::make('duplicate')->table($original))
        ->assertHasNoErrors();

    $copy = Product::whereKeyNot($original->id)->sole();

    expect($copy->sku)->toStartWith('KH-1-COPY-')
        ->and($copy->slug)->toStartWith('khanjar-1-copy-')
        ->and($copy->name)->toBe($original->name)
        ->and($copy->price_baisa)->toBe($original->price_baisa)
        ->and($copy->category_id)->toBe($original->category_id)
        ->and($copy->is_active)->toBeFalse()
        ->and($copy->is_featured)->toBeFalse();
});

it('duplicates from the edit page', function () {
    $original = Product::factory()->create();

    $this->actingAs($this->admin);

    Livewire::test(EditProduct::class, ['record' => $original->getRouteKey()])
        ->callAction('duplicate')
        ->assertHasNoErrors();

    expect(Product::count())->toBe(2);
});

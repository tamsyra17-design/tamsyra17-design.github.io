<?php

// api/tests/Feature/PublicSettingsTest.php

use App\Filament\Resources\SettingResource\Pages\CreateSetting;
use App\Filament\Resources\SettingResource\Pages\EditSetting;
use App\Filament\Resources\ShippingZoneResource\Pages\EditShippingZone;
use App\Filament\Resources\ShippingZoneResource\RelationManagers\RatesRelationManager;
use App\Models\Page;
use App\Models\Setting;
use App\Models\ShippingRate;
use App\Models\ShippingZone;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingSeeder;
use Database\Seeders\ShippingZoneSeeder;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('exposes public settings without secrets', function () {
    Setting::create(['key' => 'store_name', 'value' => ['ar' => 'تراث', 'en' => 'Heritage'], 'group' => 'general']);
    Setting::create(['key' => 'payment_secret', 'value' => 'secret', 'group' => 'private']);

    $this->getJson('/api/v1/settings/public')
        ->assertOk()
        ->assertJsonPath('data.store_name.en', 'Heritage')
        ->assertJsonMissing(['payment_secret']);
});

it('never leaks private setting keys or values', function () {
    Setting::create(['key' => 'payment_secret', 'value' => 'sk_live_123', 'group' => Setting::PRIVATE_GROUP]);
    Setting::create(['key' => 'currency', 'value' => 'OMR', 'group' => 'general']);

    $response = $this->getJson('/api/v1/settings/public')
        ->assertOk()
        ->assertJsonPath('data.currency', 'OMR')
        ->assertJsonMissingPath('data.payment_secret');

    expect($response->getContent())->not->toContain('sk_live_123');
});

it('returns an empty object when no public settings exist', function () {
    expect($this->getJson('/api/v1/settings/public')->assertOk()->getContent())
        ->toBe('{"data":{}}');
});

it('seeds the storefront defaults the public endpoint promises', function () {
    $this->seed(SettingSeeder::class);

    $this->getJson('/api/v1/settings/public')
        ->assertOk()
        ->assertJsonStructure(['data' => [
            'store_name' => ['ar', 'en'],
            'currency',
            'contact_phone',
            'contact_email',
            'social_instagram',
            'free_shipping_threshold_baisa',
            'payment_methods',
        ]])
        ->assertJsonPath('data.currency', 'OMR')
        ->assertJsonPath('data.payment_methods', ['cod']);
});

it('keeps admin edits when the setting seeder runs again', function () {
    $this->seed(SettingSeeder::class);
    Setting::where('key', 'currency')->update(['value' => json_encode('XYZ')]);

    $this->seed(SettingSeeder::class);

    expect(Setting::valueOf('currency'))->toBe('XYZ');
});

it('shows a published page with translations', function () {
    Page::factory()->create([
        'slug' => 'about',
        'title' => ['ar' => 'من نحن', 'en' => 'About us'],
        'body' => ['ar' => '<p>تراث</p>', 'en' => '<p>Heritage</p>'],
    ]);

    $this->getJson('/api/v1/pages/about')
        ->assertOk()
        ->assertJsonPath('data.slug', 'about')
        ->assertJsonPath('data.title.ar', 'من نحن')
        ->assertJsonPath('data.title.en', 'About us')
        ->assertJsonPath('data.body.en', '<p>Heritage</p>');
});

it('hides unpublished and missing pages', function () {
    Page::factory()->draft()->create(['slug' => 'draft']);

    $this->getJson('/api/v1/pages/draft')->assertNotFound();
    $this->getJson('/api/v1/pages/missing')->assertNotFound();
});

it('builds shipping zones and rates from factories', function () {
    $zone = ShippingZone::factory()->create(['governorate' => 'Muscat']);
    ShippingRate::factory()->create([
        'shipping_zone_id' => $zone->id,
        'min_weight_grams' => 0,
        'max_weight_grams' => 5000,
        'rate_baisa' => 2000,
    ]);

    expect($zone->rates)->toHaveCount(1)
        ->and($zone->rates->first()->rate_baisa)->toBe(2000)
        ->and($zone->rates->first()->free_over_baisa)->toBeNull()
        ->and($zone->rates->first()->zone->is($zone))->toBeTrue();
});

it('seeds every Oman governorate with default rates, idempotently', function () {
    $this->seed(ShippingZoneSeeder::class);
    $this->seed(ShippingZoneSeeder::class);

    expect(ShippingZone::count())->toBe(11)
        ->and(ShippingZone::where('governorate', 'Muscat')->first()->rates)->not->toBeEmpty()
        ->and(ShippingZone::doesntHave('rates')->count())->toBe(0)
        ->and(ShippingRate::count())->toBe(22);
});

it('renders the settings, pages and shipping zone admin screens', function () {
    $this->seed(RoleSeeder::class);
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $setting = Setting::create(['key' => 'store_name', 'value' => ['ar' => 'تراث', 'en' => 'Heritage'], 'group' => 'general']);
    $page = Page::factory()->create(['title' => ['ar' => 'من نحن', 'en' => 'About us']]);
    $zone = ShippingZone::factory()->create(['governorate' => 'Muscat']);
    ShippingRate::factory()->create(['shipping_zone_id' => $zone->id]);

    $this->actingAs($admin);

    $this->get('/admin/settings')->assertOk()->assertSee('store_name');
    $this->get('/admin/settings/create')->assertOk();
    $this->get("/admin/settings/{$setting->id}/edit")->assertOk();

    $this->get('/admin/pages')->assertOk()->assertSee('About us');
    $this->get('/admin/pages/create')->assertOk();
    $this->get("/admin/pages/{$page->id}/edit")->assertOk();

    $this->get('/admin/shipping-zones')->assertOk()->assertSee('Muscat');
    $this->get('/admin/shipping-zones/create')->assertOk();
    $this->get("/admin/shipping-zones/{$zone->id}/edit")->assertOk();
});

it('restricts settings, which hold secrets, to the super admin', function () {
    $this->seed(RoleSeeder::class);
    $manager = User::factory()->create();
    $manager->assignRole('content_manager');

    $this->actingAs($manager);

    $this->get('/admin/settings')->assertForbidden();
    $this->get('/admin/pages')->assertOk();
});

it('saves JSON setting values from the admin form', function () {
    $this->seed(RoleSeeder::class);
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    $this->actingAs($admin);

    Livewire::test(CreateSetting::class)
        ->fillForm([
            'key' => 'payment_methods',
            'group' => 'payments',
            'value' => '["cod", "card"]',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Setting::valueOf('payment_methods'))->toBe(['cod', 'card']);

    $setting = Setting::create(['key' => 'store_name', 'value' => ['ar' => 'تراث', 'en' => 'Heritage']]);

    Livewire::test(EditSetting::class, ['record' => $setting->getRouteKey()])
        ->assertFormSet(['value' => "{\n    \"ar\": \"تراث\",\n    \"en\": \"Heritage\"\n}"])
        ->fillForm(['value' => '{"ar": "تراث عمان", "en": "Omani Heritage"}'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($setting->fresh()->value)->toBe(['ar' => 'تراث عمان', 'en' => 'Omani Heritage']);

    Livewire::test(CreateSetting::class)
        ->fillForm(['key' => 'broken', 'group' => 'general', 'value' => '{not json'])
        ->call('create')
        ->assertHasFormErrors(['value']);
});

it('adds a weight band to a zone from the rates relation manager', function () {
    $this->seed(RoleSeeder::class);
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    $this->actingAs($admin);

    $zone = ShippingZone::factory()->create(['governorate' => 'Dhofar']);

    Livewire::test(RatesRelationManager::class, [
        'ownerRecord' => $zone,
        'pageClass' => EditShippingZone::class,
    ])
        ->callAction(TestAction::make('create')->table(), [
            'name' => 'Standard',
            'min_weight_grams' => 0,
            'max_weight_grams' => 5000,
            'rate_baisa' => 4000,
            'free_over_baisa' => 60000,
        ])
        ->assertHasNoFormErrors();

    expect($zone->rates()->sole())
        ->rate_baisa->toBe(4000)
        ->free_over_baisa->toBe(60000)
        ->max_weight_grams->toBe(5000);
});

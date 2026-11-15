# Omani Heritage Shop Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a bilingual (Arabic/English, RTL) Omani heritage shop with sell and rental flows, OMR pricing, Oman-only shipping, configurable rental policies, and a role-based admin panel.

**Architecture:** Next.js App Router frontend consumes a versioned Laravel REST API. MySQL is the single source of truth. Laravel + Filament provides the admin panel. Sale stock and rental units are separate inventory pools. All paths below are relative to `D:\xampp\htdocs\omani-shop` unless noted.

**Tech Stack:** Laravel 12 (PHP 8.3+), MySQL 8, Sanctum, Spatie Permission, Filament 3+, Next.js (App Router), TypeScript, Tailwind CSS, shadcn/ui, next-intl, React Query, Zustand, Pest, Playwright.

**Spec:** `docs/superpowers/specs/2026-11-15-omani-heritage-shop-design.md`

## Global Constraints

- Greenfield: no POS integration, migration, or shared data with `D:\xampp\htdocs` existing app.
- Arabic is the default locale and renders RTL; English is LTR.
- Currency is OMR with exactly 3 decimals. Store all money as integer **baisa** (1 OMR = 1000 baisa). Never use floats for money.
- Shipping is Oman-only. Validate governorate/wilayat.
- Guest checkout is allowed. Order/rental lookup requires number + phone.
- Sale stock and rental units are separate. Never decrement sale stock for a rental.
- Rental pricing, deposit mode, return inspection, late/damage fees, ID/contract requirements are admin-configurable.
- Admin panel is Laravel + Filament, bilingual and RTL-ready, with role-based permissions.
- Products are entered manually in admin; include duplicate/copy, defaults, image upload, and optional CSV import.
- Commit after every task. Run the listed test command before committing.
- API routes are under `/api/v1`. Frontend routes are locale-prefixed (`/ar`, `/en`).

## Review Focus

These input classes are easy to miss and expensive to fix later. Each line has a test in the task that owns the code.

1. **Arabic + LTR digits/English mixed in RTL UI** — prices, phone numbers, and English brand names must not reorder or break layout.
2. **OMR 3-decimal rounding** — cart totals, rental fees, deposits, and refunds must round consistently to baisa.
3. **Rental date boundaries** — same-day return/pickup and buffer days must not create overlapping bookings.
4. **Guest lookup isolation** — wrong phone must never reveal another customer's order/rental.
5. **Payment webhook replay** — duplicate webhook delivery must not double-credit or double-create a payment.

---

### Task 1: Scaffold Laravel API and database health

**Files:**
- Create: `api/` (Laravel project)
- Modify: `api/.env`, `api/routes/api.php`
- Create: `api/app/Http/Controllers/Api/V1/HealthController.php`
- Test: `api/tests/Feature/HealthTest.php`

**Interfaces:**
- Produces: `GET /api/v1/health` → `{"status":"ok","database":"ok"}`.
- Produces: MySQL database `omani_shop` and Laravel app key configured.

- [ ] **Step 1: Create the Laravel project**

Run:
```powershell
cd D:\xampp\htdocs\omani-shop
composer create-project laravel/laravel:^12.0 api
```

- [ ] **Step 2: Create the database and configure `.env`**

Create MySQL database `omani_shop` (utf8mb4). In `api/.env` set:
```env
APP_NAME="Omani Heritage"
APP_URL=http://localhost:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=omani_shop
DB_USERNAME=root
DB_PASSWORD=
```
Run `php artisan key:generate`.

- [ ] **Step 3: Write the failing health test**

```php
<?php
// api/tests/Feature/HealthTest.php
it('reports api and database health', function () {
    $this->getJson('/api/v1/health')
        ->assertOk()
        ->assertJson(['status' => 'ok', 'database' => 'ok']);
});
```

- [ ] **Step 4: Run the test to verify it fails**

Run: `cd api; php artisan test --filter=HealthTest`
Expected: FAIL (404).

- [ ] **Step 5: Implement the health endpoint**

Create `HealthController` with `__invoke()` returning the JSON above and checking `DB::select('select 1')`. Register in `routes/api.php`:
```php
Route::get('/v1/health', \App\Http\Controllers\Api\V1\HealthController::class);
```

- [ ] **Step 6: Run the test to verify it passes**

Run: `cd api; php artisan test --filter=HealthTest`
Expected: PASS.

- [ ] **Step 7: Commit**

```bash
git add api
git commit -m "chore: scaffold Laravel API with health endpoint"
```

---

### Task 2: Scaffold Next.js web with Arabic/English RTL

**Files:**
- Create: `web/` (Next.js project)
- Create: `web/src/i18n/routing.ts`, `web/src/i18n/request.ts`, `web/src/middleware.ts`
- Create: `web/messages/ar.json`, `web/messages/en.json`
- Create: `web/src/app/[locale]/layout.tsx`, `web/src/app/[locale]/page.tsx`
- Test: `web/e2e/smoke.spec.ts`

**Interfaces:**
- Produces: `/ar` renders `dir="rtl"` and Arabic headline; `/en` renders `dir="ltr"` and English headline.
- Produces: locale-prefixed routing with default `ar`.

- [ ] **Step 1: Create the Next.js project**

Run:
```powershell
cd D:\xampp\htdocs\omani-shop
npx create-next-app@latest web --typescript --tailwind --eslint --app --src-dir --import-alias "@/*" --use-npm
cd web
npm install next-intl @tanstack/react-query zustand react-hook-form zod @hookform/resolvers
npm install -D @playwright/test
npx playwright install
```

- [ ] **Step 2: Configure next-intl**

Create `routing.ts` with locales `['ar','en']`, default `ar`. Create `middleware.ts` using `createMiddleware(routing)`. Wrap `layout.tsx` with `NextIntlClientProvider`; set `<html lang={locale} dir={locale === 'ar' ? 'rtl' : 'ltr'}>`.

- [ ] **Step 3: Add messages**

`ar.json`: `{"home":{"title":"تراث عماني","subtitle":"بيع وتأجير القطع التراثية"}}`
`en.json`: `{"home":{"title":"Omani Heritage","subtitle":"Buy and rent heritage pieces"}}`

- [ ] **Step 4: Write the failing Playwright test**

```ts
// web/e2e/smoke.spec.ts
import { test, expect } from '@playwright/test';

test('Arabic home is RTL', async ({ page }) => {
  await page.goto('http://localhost:3000/ar');
  await expect(page.locator('html')).toHaveAttribute('dir', 'rtl');
  await expect(page.getByRole('heading', { name: 'تراث عماني' })).toBeVisible();
});

test('English home is LTR', async ({ page }) => {
  await page.goto('http://localhost:3000/en');
  await expect(page.locator('html')).toHaveAttribute('dir', 'ltr');
  await expect(page.getByRole('heading', { name: 'Omani Heritage' })).toBeVisible();
});
```

- [ ] **Step 5: Run the test to verify it fails**

Run: `cd web; npx playwright test e2e/smoke.spec.ts`
Expected: FAIL (page/heading missing).

- [ ] **Step 6: Implement the home page**

Render the translated title/subtitle in `[locale]/page.tsx`.

- [ ] **Step 7: Run the test to verify it passes**

Run: `cd web; npx playwright test e2e/smoke.spec.ts`
Expected: PASS.

- [ ] **Step 8: Commit**

```bash
git add web
git commit -m "feat: scaffold bilingual RTL Next.js frontend"
```

---

### Task 3: Shared layout, design tokens, header and footer

**Files:**
- Create: `web/src/components/layout/Header.tsx`, `Footer.tsx`, `LocaleSwitcher.tsx`
- Modify: `web/src/app/[locale]/layout.tsx`, `web/src/app/globals.css`
- Test: `web/e2e/layout.spec.ts`

**Interfaces:**
- Produces: `Header` with links to home, shop, rental, cart, account.
- Produces: `LocaleSwitcher` preserves current path when switching locale.
- Produces: CSS variables for brand colors and `font-arabic`.

- [ ] **Step 1: Write the failing test**

```ts
// web/e2e/layout.spec.ts
import { test, expect } from '@playwright/test';

test('header and locale switch work', async ({ page }) => {
  await page.goto('http://localhost:3000/ar');
  await expect(page.getByRole('link', { name: 'المتجر' })).toBeVisible();
  await page.getByRole('link', { name: 'English' }).click();
  await expect(page).toHaveURL(/\/en/);
  await expect(page.locator('html')).toHaveAttribute('dir', 'ltr');
});
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `cd web; npx playwright test e2e/layout.spec.ts`
Expected: FAIL.

- [ ] **Step 3: Implement layout components**

Use semantic `<header>`, `<nav>`, `<main>`, `<footer>`. Add Arabic/English labels to messages. Use `usePathname` + `useRouter` from `next-intl/navigation` for the switcher. Add focus-visible styles and skip link.

- [ ] **Step 4: Run the test to verify it passes**

Run: `cd web; npx playwright test e2e/layout.spec.ts`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add web
git commit -m "feat: add shared layout, header, footer, locale switcher"
```

---

### Task 4: Customer auth, staff auth, roles and permissions

**Files:**
- Create: `api/app/Http/Controllers/Api/V1/AuthController.php`
- Create: `api/app/Http/Requests/Auth/RegisterRequest.php`, `LoginRequest.php`
- Create: `api/database/seeders/RoleSeeder.php`, `AdminUserSeeder.php`
- Modify: `api/routes/api.php`, `api/app/Models/User.php`
- Test: `api/tests/Feature/AuthTest.php`, `api/tests/Feature/RoleTest.php`

**Interfaces:**
- Produces: `POST /api/v1/auth/register` (`name, email, phone, password`).
- Produces: `POST /api/v1/auth/login` → `{token, user}`.
- Produces: `POST /api/v1/auth/logout` (auth:sanctum).
- Produces: roles `super_admin`, `catalog_manager`, `order_manager`, `rental_manager`, `content_manager`, `support`.

- [ ] **Step 1: Install Sanctum and Spatie Permission**

Run:
```powershell
cd api
composer require laravel/sanctum spatie/laravel-permission
php artisan install:api
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

- [ ] **Step 2: Write failing tests**

```php
<?php
// api/tests/Feature/AuthTest.php
it('registers a customer and returns a token', function () {
    $this->postJson('/api/v1/auth/register', [
        'name' => 'Salim', 'email' => 'salim@example.com',
        'phone' => '+96890000000', 'password' => 'secret123',
    ])->assertCreated()->assertJsonStructure(['token', 'user' => ['id', 'name']]);
});

it('rejects a wrong password', function () {
    $user = \App\Models\User::factory()->create(['password' => bcrypt('secret123')]);
    $this->postJson('/api/v1/auth/login', ['email' => $user->email, 'password' => 'wrong'])
        ->assertUnprocessable();
});
```

```php
<?php
// api/tests/Feature/RoleTest.php
it('seeds staff roles', function () {
    $this->seed(\Database\Seeders\RoleSeeder::class);
    expect(\Spatie\Permission\Models\Role::pluck('name')->all())
        ->toContain('super_admin', 'catalog_manager', 'rental_manager');
});
```

- [ ] **Step 3: Run the tests to verify they fail**

Run: `cd api; php artisan test --filter="AuthTest|RoleTest"`
Expected: FAIL.

- [ ] **Step 4: Implement auth and seeds**

Use `HasApiTokens` on `User`. Validate unique email/phone. Return Sanctum token. Seed roles and one `super_admin` from env `ADMIN_EMAIL`/`ADMIN_PASSWORD`. Register routes.

- [ ] **Step 5: Run the tests to verify they pass**

Run: `cd api; php artisan test --filter="AuthTest|RoleTest"`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add api
git commit -m "feat: add customer auth, staff auth, roles and permissions"
```

---

### Task 5: Filament admin panel with role-based access

**Files:**
- Create: `api/app/Filament/` (panel)
- Modify: `api/app/Providers/Filament/AdminPanelProvider.php`
- Create: `api/app/Policies/` and `api/app/Filament/Resources/UserResource.php`
- Test: `api/tests/Feature/AdminAccessTest.php`

**Interfaces:**
- Produces: admin panel at `/admin`, login required.
- Produces: `super_admin` can manage users; other roles cannot.
- Produces: RTL admin layout when locale is `ar`.

- [ ] **Step 1: Install Filament**

Run:
```powershell
cd api
composer require filament/filament
php artisan filament:install --panels
```

- [ ] **Step 2: Write the failing test**

```php
<?php
// api/tests/Feature/AdminAccessTest.php
it('blocks a customer from the admin panel', function () {
    $user = \App\Models\User::factory()->create();
    $this->actingAs($user)->get('/admin')->assertForbidden();
});

it('allows a super admin into the admin panel', function () {
    $this->seed(\Database\Seeders\RoleSeeder::class);
    $admin = \App\Models\User::factory()->create();
    $admin->assignRole('super_admin');
    $this->actingAs($admin)->get('/admin')->assertOk();
});
```

- [ ] **Step 3: Run the test to verify it fails**

Run: `cd api; php artisan test --filter=AdminAccessTest`
Expected: FAIL.

- [ ] **Step 4: Implement panel access**

In `AdminPanelProvider`, set `->path('admin')`, `->login()`, and `->authGuard('web')`. Implement `canAccessPanel()` on `User` returning `$this->hasAnyRole([...staff roles])`. Add `UserResource` guarded by `super_admin` permission.

- [ ] **Step 5: Run the test to verify it passes**

Run: `cd api; php artisan test --filter=AdminAccessTest`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add api
git commit -m "feat: add Filament admin with role-based access"
```

---

### Task 6: Categories (model, API, admin)

**Files:**
- Create: `api/database/migrations/*_create_categories_table.php`
- Create: `api/app/Models/Category.php`
- Create: `api/app/Http/Controllers/Api/V1/CategoryController.php`
- Create: `api/app/Http/Resources/CategoryResource.php`
- Create: `api/app/Filament/Resources/CategoryResource.php`
- Create: `api/database/factories/CategoryFactory.php`
- Test: `api/tests/Feature/CategoryApiTest.php`

**Interfaces:**
- Produces: `GET /api/v1/categories` → active categories with `id, slug, name{ar,en}, parent_id, is_rental_only`.
- Produces: `Category` fields: `slug`, `name` JSON, `description` JSON, `parent_id`, `image_path`, `sort_order`, `is_active`, `is_rental_only`.

- [ ] **Step 1: Write the failing test**

```php
<?php
// api/tests/Feature/CategoryApiTest.php
it('lists active categories with translations', function () {
    \App\Models\Category::factory()->create(['name' => ['ar' => 'خناجر', 'en' => 'Khanjars']]);
    \App\Models\Category::factory()->create(['is_active' => false]);

    $this->getJson('/api/v1/categories')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name.ar', 'خناجر')
        ->assertJsonPath('data.0.name.en', 'Khanjars');
});
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `cd api; php artisan test --filter=CategoryApiTest`
Expected: FAIL.

- [ ] **Step 3: Create migration, model, factory, resource, controller**

Migration includes `id, parent_id nullable, slug unique, name json, description json nullable, image_path nullable, sort_order default 0, is_active default true, is_rental_only default false, timestamps`. Cast `name`/`description` to `array`. Controller returns ordered active categories.

- [ ] **Step 4: Add Filament resource**

Form fields: slug, Arabic/English name, description, parent, image, sort order, active, rental-only. Table columns: name.ar, name.en, slug, active.

- [ ] **Step 5: Run the test to verify it passes**

Run: `cd api; php artisan test --filter=CategoryApiTest`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add api
git commit -m "feat: add categories model, API and admin resource"
```

---

### Task 7: Products with translations, sale price and API

**Files:**
- Create: `api/database/migrations/*_create_products_table.php`
- Create: `api/app/Models/Product.php`
- Create: `api/app/Http/Controllers/Api/V1/ProductController.php`
- Create: `api/app/Http/Resources/ProductResource.php`, `ProductDetailResource.php`
- Create: `api/app/Filament/Resources/ProductResource.php`
- Create: `api/database/factories/ProductFactory.php`
- Test: `api/tests/Feature/ProductApiTest.php`

**Interfaces:**
- Produces: `GET /api/v1/products?category=&q=&rentable=&page=` → paginated active products.
- Produces: `GET /api/v1/products/{slug}` → detail with images, variants, category, sale/rental availability flags.
- Produces: `Product` fields: `category_id`, `sku`, `slug`, `name` JSON, `short_description` JSON, `description` JSON, `price_baisa` nullable, `is_active`, `is_rentable`, `is_featured`, `weight_grams`, `search_text`.

- [ ] **Step 1: Write the failing test**

```php
<?php
// api/tests/Feature/ProductApiTest.php
it('lists products and filters by category', function () {
    $cat = \App\Models\Category::factory()->create();
    \App\Models\Product::factory()->create(['category_id' => $cat->id, 'price_baisa' => 12500]);
    \App\Models\Product::factory()->create();

    $this->getJson('/api/v1/products?category=' . $cat->slug)
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.price_baisa', 12500);
});

it('shows a product by slug', function () {
    $p = \App\Models\Product::factory()->create(['slug' => 'khanjar-1']);
    $this->getJson('/api/v1/products/khanjar-1')->assertOk()->assertJsonPath('data.slug', 'khanjar-1');
});
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `cd api; php artisan test --filter=ProductApiTest`
Expected: FAIL.

- [ ] **Step 3: Create migration, model, factory, resources, controller**

Migration fields as above. `Product` belongs to `Category`, has many `ProductImage`/`ProductVariant`. Cast translations to array. Controller filters active by category slug and `is_rentable`. Use `ProductResource` for lists and `ProductDetailResource` for detail.

- [ ] **Step 4: Add Filament resource**

Form sections: Basic (sku, slug, category, translations), Pricing (price_baisa, weight_grams), Flags (active, rentable, featured). Table: primary image, name.ar, sku, price, active, rentable. Add "Duplicate" action that clones the product without images.

- [ ] **Step 5: Run the test to verify it passes**

Run: `cd api; php artisan test --filter=ProductApiTest`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add api
git commit -m "feat: add products with translations and public API"
```

---

### Task 8: Product images, variants and sale inventory

**Files:**
- Create: `api/database/migrations/*_create_product_images_table.php`, `*_create_product_variants_table.php`
- Create: `api/app/Models/ProductImage.php`, `ProductVariant.php`
- Modify: `api/app/Models/Product.php`
- Create: `api/app/Filament/Resources/ProductResource/RelationManagers/ImagesRelationManager.php`, `VariantsRelationManager.php`
- Test: `api/tests/Feature/ProductVariantTest.php`

**Interfaces:**
- Produces: `product_images` (`path`, `alt` JSON, `sort_order`, `is_primary`).
- Produces: `product_variants` (`sku`, `name` JSON, `price_baisa`, `sale_stock`, `attributes` JSON, `is_active`).
- Produces: `Product::inStock(): bool` and `Product::saleStock(): int`.

- [ ] **Step 1: Write the failing test**

```php
<?php
// api/tests/Feature/ProductVariantTest.php
it('reports sale stock across variants', function () {
    $p = \App\Models\Product::factory()->create();
    \App\Models\ProductVariant::factory()->create(['product_id' => $p->id, 'sale_stock' => 3]);
    \App\Models\ProductVariant::factory()->create(['product_id' => $p->id, 'sale_stock' => 2]);
    expect($p->fresh()->saleStock())->toBe(5);
    expect($p->fresh()->inStock())->toBeTrue();
});
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `cd api; php artisan test --filter=ProductVariantTest`
Expected: FAIL.

- [ ] **Step 3: Create migrations, models, relations and methods**

Add `images()` and `variants()` to `Product`. `saleStock()` sums active variants; `inStock()` returns `saleStock() > 0`.

- [ ] **Step 4: Add Filament relation managers**

Images: upload to `products/{id}`, alt ar/en, primary toggle. Variants: repeatable with sku, name ar/en, price, stock, attributes JSON.

- [ ] **Step 5: Run the test to verify it passes**

Run: `cd api; php artisan test --filter=ProductVariantTest`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add api
git commit -m "feat: add product images, variants and sale stock"
```

---

### Task 9: Arabic-tolerant search and product filters

**Files:**
- Create: `api/app/Support/ArabicNormalizer.php`
- Create: `api/app/Observers/ProductObserver.php`
- Create: `api/database/migrations/*_add_search_text_to_products_table.php`
- Modify: `api/app/Models/Product.php`, `api/app/Http/Controllers/Api/V1/ProductController.php`
- Test: `api/tests/Unit/ArabicNormalizerTest.php`, `api/tests/Feature/ProductSearchTest.php`

**Interfaces:**
- Produces: `ArabicNormalizer::normalize(string $text): string` — strips diacritics/tatweel, unifies alef/hamza, ta marbuta → ha, ya → alef maqsura.
- Produces: `GET /api/v1/products?q=خنجر` matches `خِنْجَر`, `خنجر`, `خنجر` variants.

- [ ] **Step 1: Write the failing unit test**

```php
<?php
// api/tests/Unit/ArabicNormalizerTest.php
it('normalizes Arabic spelling variants', function () {
    expect(\App\Support\ArabicNormalizer::normalize('خِنْجَر'))->toBe('خنجر');
    expect(\App\Support\ArabicNormalizer::normalize('خَنجَر'))->toBe('خنجر');
    expect(\App\Support\ArabicNormalizer::normalize('مُصَرَّة'))->toBe('مصره');
});
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `cd api; php artisan test --filter=ArabicNormalizerTest`
Expected: FAIL.

- [ ] **Step 3: Implement normalizer and observer**

Normalizer uses `preg_replace` for `[\x{064B}-\x{065F}\x{0670}\x{06D6}-\x{06ED}\x{0640}]`, then strtr for `أإآٱ` → `ا`, `ى` → `ي`, `ة` → `ه`. Observer sets `search_text` to normalized `name.ar + name.en + sku + description.ar`. Register observer in `Product::booted()`.

- [ ] **Step 4: Write the failing feature test**

```php
<?php
// api/tests/Feature/ProductSearchTest.php
it('finds a product by normalized Arabic query', function () {
    \App\Models\Product::factory()->create(['name' => ['ar' => 'خِنْجَر فاخر', 'en' => 'Luxury Khanjar']]);
    $this->getJson('/api/v1/products?q=خنجر')->assertOk()->assertJsonCount(1, 'data');
});
```

- [ ] **Step 5: Run the feature test to verify it fails**

Run: `cd api; php artisan test --filter=ProductSearchTest`
Expected: FAIL.

- [ ] **Step 6: Apply search filter**

In `ProductController@index`, when `q` is present, normalize it and query `where('search_text', 'like', "%{$q}%")` or MySQL full-text `MATCH AGAINST`. Run migration to backfill `search_text`.

- [ ] **Step 7: Run both tests to verify they pass**

Run: `cd api; php artisan test --filter="ArabicNormalizerTest|ProductSearchTest"`
Expected: PASS.

- [ ] **Step 8: Commit**

```bash
git add api
git commit -m "feat: add Arabic-tolerant product search"
```

---

### Task 10: Settings, pages, shipping zones and rates

**Files:**
- Create: `api/database/migrations/*_create_settings_table.php`, `*_create_pages_table.php`, `*_create_shipping_zones_table.php`, `*_create_shipping_rates_table.php`
- Create: `api/app/Models/Setting.php`, `Page.php`, `ShippingZone.php`, `ShippingRate.php`
- Create: `api/app/Http/Controllers/Api/V1/SettingController.php`, `PageController.php`
- Create: `api/app/Filament/Resources/SettingResource.php`, `PageResource.php`, `ShippingZoneResource.php`
- Test: `api/tests/Feature/PublicSettingsTest.php`

**Interfaces:**
- Produces: `GET /api/v1/settings/public` → store name, currency, contact, social, free-shipping threshold, enabled payment methods.
- Produces: `GET /api/v1/pages/{slug}` → published page with `title`/`body` translations.
- Produces: `shipping_zones` (governorate) and `shipping_rates` (weight band + rate_baisa).

- [ ] **Step 1: Write the failing test**

```php
<?php
// api/tests/Feature/PublicSettingsTest.php
it('exposes public settings without secrets', function () {
    \App\Models\Setting::create(['key' => 'store_name', 'value' => ['ar' => 'تراث', 'en' => 'Heritage'], 'group' => 'general']);
    \App\Models\Setting::create(['key' => 'payment_secret', 'value' => 'secret', 'group' => 'private']);

    $this->getJson('/api/v1/settings/public')
        ->assertOk()
        ->assertJsonPath('data.store_name.en', 'Heritage')
        ->assertJsonMissing(['payment_secret']);
});
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `cd api; php artisan test --filter=PublicSettingsTest`
Expected: FAIL.

- [ ] **Step 3: Create migrations, models, controllers**

`settings`: `key` unique, `value` json, `group`. `pages`: `slug`, `title` json, `body` json, `is_published`. `shipping_zones`: `name` json, `governorate` unique, `is_active`. `shipping_rates`: `shipping_zone_id`, `name`, `min_weight_grams`, `max_weight_grams`, `rate_baisa`, `free_over_baisa` nullable.

- [ ] **Step 4: Add Filament resources**

Settings grouped tabs; pages rich editor; shipping zone with rates relation manager. Seed Oman governorates and default rates.

- [ ] **Step 5: Run the test to verify it passes**

Run: `cd api; php artisan test --filter=PublicSettingsTest`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add api
git commit -m "feat: add settings, pages and Oman shipping rates"
```

---

### Task 11: Storefront catalog pages

**Files:**
- Create: `web/src/lib/api.ts`, `web/src/lib/types.ts`, `web/src/lib/money.ts`
- Create: `web/src/components/product/ProductCard.tsx`, `ProductGrid.tsx`, `Price.tsx`
- Create: `web/src/app/[locale]/shop/page.tsx`, `web/src/app/[locale]/categories/[slug]/page.tsx`
- Test: `web/e2e/catalog.spec.ts`

**Interfaces:**
- Produces: `apiGet<T>(path, params?)` typed fetch wrapper.
- Produces: `formatOmr(baisa: number): string` → `"OMR 12.500"` (3 decimals, locale-aware).
- Consumes: `GET /api/v1/products`, `GET /api/v1/categories`.

- [ ] **Step 1: Write the failing test**

```ts
// web/e2e/catalog.spec.ts
import { test, expect } from '@playwright/test';

test('shop lists products and formats OMR', async ({ page }) => {
  await page.goto('http://localhost:3000/ar/shop');
  await expect(page.getByTestId('product-card').first()).toBeVisible();
  await expect(page.getByTestId('price').first()).toContainText('ر.ع.');
});
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `cd web; npx playwright test e2e/catalog.spec.ts`
Expected: FAIL.

- [ ] **Step 3: Implement API client, money helper, cards and pages**

`formatOmr` divides baisa by 1000 and uses `Intl.NumberFormat('ar-OM', { style: 'currency', currency: 'OMR', minimumFractionDigits: 3 })`. ProductCard shows image, localized name, price, and rental badge if `is_rentable`. Pages fetch server-side with `cache: 'no-store'` in dev.

- [ ] **Step 4: Run the test to verify it passes**

Run: `cd web; npx playwright test e2e/catalog.spec.ts`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add web
git commit -m "feat: add storefront catalog pages and OMR formatting"
```

---

### Task 12: Product detail page and cart store

**Files:**
- Create: `web/src/app/[locale]/products/[slug]/page.tsx`
- Create: `web/src/components/product/ProductGallery.tsx`, `AddToCartButton.tsx`
- Create: `web/src/stores/cart.ts`
- Test: `web/e2e/product-detail.spec.ts`, `web/src/stores/cart.test.ts`

**Interfaces:**
- Produces: `useCartStore` with `items`, `addItem(product, variant, qty)`, `removeItem`, `clear`, `subtotalBaisa`.
- Produces: product detail page with gallery, price, stock state, add-to-cart, rental CTA if rentable.

- [ ] **Step 1: Write the failing cart unit test**

```ts
// web/src/stores/cart.test.ts
import { describe, it, expect, beforeEach } from 'vitest';
import { useCartStore } from './cart';

beforeEach(() => useCartStore.setState({ items: [] }));

it('adds items and sums subtotal', () => {
  const { addItem } = useCartStore.getState();
  addItem({ productId: 1, variantId: 1, name: 'خنجر', priceBaisa: 12500, qty: 2 });
  expect(useCartStore.getState().subtotalBaisa()).toBe(25000);
});
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `cd web; npx vitest run src/stores/cart.test.ts`
Expected: FAIL.

- [ ] **Step 3: Implement cart store**

Use Zustand with `persist` middleware (localStorage key `omani-cart`). Merge duplicate lines by `productId+variantId`. Money stays integer baisa.

- [ ] **Step 4: Write the failing E2E test**

```ts
// web/e2e/product-detail.spec.ts
import { test, expect } from '@playwright/test';

test('adds a product to the cart', async ({ page }) => {
  await page.goto('http://localhost:3000/en/shop');
  await page.getByTestId('product-card').first().click();
  await page.getByTestId('add-to-cart').click();
  await expect(page.getByTestId('cart-count')).toHaveText('1');
});
```

- [ ] **Step 5: Run the E2E test to verify it fails**

Run: `cd web; npx playwright test e2e/product-detail.spec.ts`
Expected: FAIL.

- [ ] **Step 6: Implement detail page and button**

Fetch `GET /api/v1/products/{slug}`. Disable add-to-cart when `!inStock`. Show rental CTA linking to `/rental/{slug}`. Header cart count reads Zustand.

- [ ] **Step 7: Run tests to verify they pass**

Run: `cd web; npx vitest run src/stores/cart.test.ts; npx playwright test e2e/product-detail.spec.ts`
Expected: PASS.

- [ ] **Step 8: Commit**

```bash
git add web
git commit -m "feat: add product detail page and persistent cart"
```

---

### Task 13: Cart page and checkout shell

**Files:**
- Create: `web/src/app/[locale]/cart/page.tsx`
- Create: `web/src/components/cart/CartLine.tsx`, `CartSummary.tsx`
- Create: `web/src/app/[locale]/checkout/page.tsx`
- Test: `web/e2e/cart.spec.ts`

**Interfaces:**
- Produces: cart page with quantity update/remove, subtotal, shipping estimate, checkout link.
- Produces: checkout page guarded against empty cart.

- [ ] **Step 1: Write the failing test**

```ts
// web/e2e/cart.spec.ts
import { test, expect } from '@playwright/test';

test('updates quantity and shows total in OMR', async ({ page }) => {
  await page.goto('http://localhost:3000/en/shop');
  await page.getByTestId('product-card').first().click();
  await page.getByTestId('add-to-cart').click();
  await page.goto('http://localhost:3000/en/cart');
  await page.getByTestId('qty-plus').click();
  await expect(page.getByTestId('cart-subtotal')).toContainText('OMR');
});
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `cd web; npx playwright test e2e/cart.spec.ts`
Expected: FAIL.

- [ ] **Step 3: Implement cart and checkout shell**

Cart line shows localized name, unit price, qty stepper, line total, remove. Summary shows subtotal and "shipping calculated at checkout". Checkout shows address/payment placeholders and disabled submit until Task 14.

- [ ] **Step 4: Run the test to verify it passes**

Run: `cd web; npx playwright test e2e/cart.spec.ts`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add web
git commit -m "feat: add cart page and checkout shell"
```

---

### Task 14: Order creation API with COD and bank transfer

**Files:**
- Create: `api/database/migrations/*_create_orders_table.php`, `*_create_order_items_table.php`, `*_create_payments_table.php`
- Create: `api/app/Models/Order.php`, `OrderItem.php`, `Payment.php`
- Create: `api/app/Http/Controllers/Api/V1/OrderController.php`
- Create: `api/app/Http/Requests/StoreOrderRequest.php`
- Create: `api/app/Services/OrderNumberGenerator.php`
- Create: `api/app/Services/ShippingCalculator.php`
- Test: `api/tests/Feature/OrderApiTest.php`

**Interfaces:**
- Produces: `POST /api/v1/orders` with `customer{name,phone,email}, address{governorate,wilayat,line1,line2,postal_code}, items[{variant_id,qty}], payment_method`.
- Produces: `Order` with `number`, totals in baisa, snapshot items, `status`, `payment_status`.
- Produces: `ShippingCalculator::rateFor(string $governorate, int $weightGrams, int $subtotalBaisa): int`.

- [ ] **Step 1: Write the failing test**

```php
<?php
// api/tests/Feature/OrderApiTest.php
it('creates a COD order and decrements stock', function () {
    $p = \App\Models\Product::factory()->create(['price_baisa' => 10000]);
    $v = \App\Models\ProductVariant::factory()->create(['product_id' => $p->id, 'sale_stock' => 5]);
    \App\Models\ShippingRate::factory()->create(['governorate' => 'Muscat', 'rate_baisa' => 2000, 'min_weight_grams' => 0, 'max_weight_grams' => 5000]);

    $this->postJson('/api/v1/orders', [
        'customer' => ['name' => 'Salim', 'phone' => '+96890000000', 'email' => 's@example.com'],
        'address' => ['governorate' => 'Muscat', 'wilayat' => 'Muscat', 'line1' => 'Al Khuwair'],
        'items' => [['variant_id' => $v->id, 'qty' => 2]],
        'payment_method' => 'cod',
    ])->assertCreated()->assertJsonPath('data.total_baisa', 22000);

    expect($v->fresh()->sale_stock)->toBe(3);
});
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `cd api; php artisan test --filter=OrderApiTest`
Expected: FAIL.

- [ ] **Step 3: Create migrations and models**

`orders`: number unique, customer fields, address JSON snapshot, subtotal/shipping/total baisa, status, payment_status, payment_method, placed_at. `order_items`: order_id, product_id, variant_id, name snapshot JSON, sku, unit_price_baisa, qty, line_total_baisa. `payments`: payable morph, gateway, method, amount_baisa, status, transaction_ref, payload JSON, paid_at.

- [ ] **Step 4: Implement order service**

Validate stock in a DB transaction with `lockForUpdate()`. Calculate subtotal, shipping, total. Create order/items/payment. Decrement variant stock. Return `OrderResource`.

- [ ] **Step 5: Run the test to verify it passes**

Run: `cd api; php artisan test --filter=OrderApiTest`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add api
git commit -m "feat: add order creation with COD and stock decrement"
```

---

### Task 15: Checkout UI, confirmation and guest order tracking

**Files:**
- Modify: `web/src/app/[locale]/checkout/page.tsx`
- Create: `web/src/app/[locale]/orders/[number]/page.tsx`
- Create: `web/src/app/[locale]/track/page.tsx`
- Create: `web/src/components/checkout/AddressForm.tsx`, `PaymentMethodPicker.tsx`
- Test: `web/e2e/checkout.spec.ts`

**Interfaces:**
- Produces: checkout submits `POST /api/v1/orders`; clears cart; redirects to `/orders/{number}`.
- Produces: tracking form `number + phone` → `GET /api/v1/orders/{number}?phone=`.
- Produces: order detail shows status, items, totals, shipping address.

- [ ] **Step 1: Write the failing test**

```ts
// web/e2e/checkout.spec.ts
import { test, expect } from '@playwright/test';

test('guest can place a COD order and see confirmation', async ({ page }) => {
  await page.goto('http://localhost:3000/en/shop');
  await page.getByTestId('product-card').first().click();
  await page.getByTestId('add-to-cart').click();
  await page.goto('http://localhost:3000/en/checkout');
  await page.getByLabel('Name').fill('Salim');
  await page.getByLabel('Phone').fill('+96890000000');
  await page.getByLabel('Governorate').selectOption('Muscat');
  await page.getByLabel('Wilayat').fill('Muscat');
  await page.getByLabel('Address').fill('Al Khuwair');
  await page.getByTestId('place-order').click();
  await expect(page.getByTestId('order-number')).toContainText('ORD-');
});
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `cd web; npx playwright test e2e/checkout.spec.ts`
Expected: FAIL.

- [ ] **Step 3: Implement address form, payment picker and submit**

Use React Hook Form + Zod. Governorate select from `/api/v1/settings/public` or hardcoded Oman list; wilayat text. On success clear cart and navigate.

- [ ] **Step 4: Implement order detail and guest tracking**

`GET /api/v1/orders/{number}?phone=` returns 404 unless phone matches the order. Never expose email/address without match. Show order status timeline.

- [ ] **Step 5: Run the test to verify it passes**

Run: `cd web; npx playwright test e2e/checkout.spec.ts`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add web
git commit -m "feat: add guest checkout, confirmation and order tracking"
```

---

### Task 16: Rental policies and admin management

**Files:**
- Create: `api/database/migrations/*_create_rental_policies_table.php`
- Create: `api/app/Models/RentalPolicy.php`
- Create: `api/app/Filament/Resources/RentalPolicyResource.php`
- Create: `api/database/seeders/RentalPolicySeeder.php`
- Test: `api/tests/Feature/RentalPolicyTest.php`

**Interfaces:**
- Produces: `RentalPolicy` fields: `name`, `base_days`, `base_fee_baisa`, `extra_daily_baisa`, `weekly_fee_baisa` nullable, `deposit_baisa`, `deposit_mode` (`hold|charge|cash|bank_transfer`), `return_day_inclusive`, `buffer_days`, `min_days`, `max_days`, `late_fee_per_day_baisa`, `damage_fee_notes`, `require_id`, `require_contract`, `inspection_days`, `is_default`.
- Produces: `RentalPolicy::default(): self`.

- [ ] **Step 1: Write the failing test**

```php
<?php
// api/tests/Feature/RentalPolicyTest.php
it('returns the default rental policy', function () {
    $this->seed(\Database\Seeders\RentalPolicySeeder::class);
    $policy = \App\Models\RentalPolicy::default();
    expect($policy)->not->toBeNull();
    expect($policy->base_days)->toBe(1);
    expect($policy->deposit_mode)->toBe('charge');
});
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `cd api; php artisan test --filter=RentalPolicyTest`
Expected: FAIL.

- [ ] **Step 3: Create migration, model, seeder and Filament resource**

Default policy: `base_days=1`, `base_fee_baisa=5000`, `extra_daily_baisa=3000`, `deposit_baisa=20000`, `deposit_mode=charge`, `return_day_inclusive=false`, `buffer_days=1`, `min_days=1`, `max_days=30`, `inspection_days=2`. Admin form exposes every field.

- [ ] **Step 4: Run the test to verify it passes**

Run: `cd api; php artisan test --filter=RentalPolicyTest`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add api
git commit -m "feat: add configurable rental policies"
```

---

### Task 17: Rental units and availability API

**Files:**
- Create: `api/database/migrations/*_create_rental_units_table.php`, `*_create_rental_bookings_table.php`
- Create: `api/app/Models/RentalUnit.php`
- Create: `api/app/Services/RentalAvailabilityService.php`
- Create: `api/app/Http/Controllers/Api/V1/RentalAvailabilityController.php`
- Create: `api/app/Filament/Resources/RentalUnitResource.php`
- Test: `api/tests/Feature/RentalAvailabilityTest.php`

**Interfaces:**
- Produces: `rental_units` (`product_id`, `code`, `status`, `notes`).
- Produces: `rental_bookings` (`rental_unit_id`, `start_date`, `end_date`, `rental_id`).
- Produces: `RentalAvailabilityService::availableUnits(Product $p, Carbon $start, Carbon $end): Collection`.
- Produces: `GET /api/v1/rental/products/{slug}/availability?from=&to=` → `{available: int, units: [...]}`.

- [ ] **Step 1: Write the failing test**

```php
<?php
// api/tests/Feature/RentalAvailabilityTest.php
it('excludes booked units and respects buffer days', function () {
    $p = \App\Models\Product::factory()->create(['is_rentable' => true]);
    $u1 = \App\Models\RentalUnit::factory()->create(['product_id' => $p->id]);
    \App\Models\RentalUnit::factory()->create(['product_id' => $p->id]);
    \App\Models\RentalBooking::create([
        'rental_unit_id' => $u1->id,
        'start_date' => '2026-12-01',
        'end_date' => '2026-12-03',
    ]);

    $available = app(\App\Services\RentalAvailabilityService::class)
        ->availableUnits($p, \Carbon\Carbon::parse('2026-12-03'), \Carbon\Carbon::parse('2026-12-05'));

    expect($available)->toHaveCount(1);
});
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `cd api; php artisan test --filter=RentalAvailabilityTest`
Expected: FAIL.

- [ ] **Step 3: Create migrations, models and service**

Availability query excludes units with a booking where `start_date <= requested_end + buffer` and `end_date >= requested_start - buffer`. Use `whereNotIn`. Return units.

- [ ] **Step 4: Implement API and Filament resource**

Controller validates dates (`after_or_equal:today`, `after:from`), loads product by slug, returns count and unit codes. Filament unit resource: product, code, status, notes, block dates action.

- [ ] **Step 5: Run the test to verify it passes**

Run: `cd api; php artisan test --filter=RentalAvailabilityTest`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add api
git commit -m "feat: add rental units and availability service"
```

---

### Task 18: Rental pricing service and quote API

**Files:**
- Create: `api/app/Services/RentalPricingService.php`
- Create: `api/app/Data/RentalQuote.php`
- Create: `api/app/Http/Controllers/Api/V1/RentalQuoteController.php`
- Test: `api/tests/Unit/RentalPricingTest.php`, `api/tests/Feature/RentalQuoteApiTest.php`

**Interfaces:**
- Produces: `RentalQuote` with `days`, `base_fee_baisa`, `extra_days`, `extra_fee_baisa`, `deposit_baisa`, `total_baisa`.
- Produces: `RentalPricingService::quote(Product $product, Carbon $start, Carbon $end, RentalPolicy $policy): RentalQuote`.
- Produces: `POST /api/v1/rental/quote` with `product_slug, from, to` → quote JSON.

- [ ] **Step 1: Write the failing unit test**

```php
<?php
// api/tests/Unit/RentalPricingTest.php
it('charges base fee plus extra days', function () {
    $p = \App\Models\Product::factory()->make();
    $policy = new \App\Models\RentalPolicy([
        'base_days' => 2, 'base_fee_baisa' => 10000, 'extra_daily_baisa' => 3000,
        'deposit_baisa' => 20000, 'return_day_inclusive' => false,
    ]);

    $quote = app(\App\Services\RentalPricingService::class)
        ->quote($p, \Carbon\Carbon::parse('2026-12-01'), \Carbon\Carbon::parse('2026-12-05'), $policy);

    expect($quote->days)->toBe(5);
    expect($quote->extra_days)->toBe(3);
    expect($quote->total_baisa)->toBe(29000); // 10000 + 3*3000 + 20000 deposit
});
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `cd api; php artisan test --filter=RentalPricingTest`
Expected: FAIL.

- [ ] **Step 3: Implement pricing service**

Days = `start->diffInDays($end) + ($policy->return_day_inclusive ? 1 : 0)`. Extra days = max(0, days - base_days). Use weekly rate when configured and cheaper. Total includes deposit. All math in integer baisa.

- [ ] **Step 4: Write the failing API test**

```php
<?php
// api/tests/Feature/RentalQuoteApiTest.php
it('returns a rental quote', function () {
    $p = \App\Models\Product::factory()->create(['slug' => 'khanjar', 'is_rentable' => true]);
    \App\Models\RentalPolicy::factory()->create(['is_default' => true]);

    $this->postJson('/api/v1/rental/quote', [
        'product_slug' => 'khanjar', 'from' => '2026-12-01', 'to' => '2026-12-03',
    ])->assertOk()->assertJsonStructure(['data' => ['days', 'total_baisa']]);
});
```

- [ ] **Step 5: Implement controller and route**

Validate dates, find rentable product, use default policy, return quote. Return 422 if duration outside min/max.

- [ ] **Step 6: Run tests to verify they pass**

Run: `cd api; php artisan test --filter="RentalPricingTest|RentalQuoteApiTest"`
Expected: PASS.

- [ ] **Step 7: Commit**

```bash
git add api
git commit -m "feat: add configurable rental pricing and quote API"
```

---

### Task 19: Rental storefront and calendar

**Files:**
- Create: `web/src/app/[locale]/rental/page.tsx`
- Create: `web/src/app/[locale]/rental/[slug]/page.tsx`
- Create: `web/src/components/rental/RentalCalendar.tsx`, `RentalQuoteCard.tsx`
- Test: `web/e2e/rental.spec.ts`

**Interfaces:**
- Consumes: `GET /api/v1/products?rentable=1`, `GET /api/v1/rental/products/{slug}/availability`, `POST /api/v1/rental/quote`.
- Produces: rental listing and detail with date range picker, live quote, deposit display, "Book rental" CTA.

- [ ] **Step 1: Write the failing test**

```ts
// web/e2e/rental.spec.ts
import { test, expect } from '@playwright/test';

test('rental page shows quote after selecting dates', async ({ page }) => {
  await page.goto('http://localhost:3000/en/rental');
  await page.getByTestId('rental-card').first().click();
  await page.getByTestId('rental-from').fill('2026-12-01');
  await page.getByTestId('rental-to').fill('2026-12-03');
  await expect(page.getByTestId('rental-total')).toContainText('OMR');
});
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `cd web; npx playwright test e2e/rental.spec.ts`
Expected: FAIL.

- [ ] **Step 3: Implement rental listing and detail**

Listing uses rental product API with badge "For rent". Detail has date inputs, availability message, quote card (days, base fee, extra days, deposit, total), and CTA. Disable CTA when unavailable or invalid range.

- [ ] **Step 4: Run the test to verify it passes**

Run: `cd web; npx playwright test e2e/rental.spec.ts`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add web
git commit -m "feat: add rental storefront with availability and live quote"
```

---

### Task 20: Rental booking API, deposits and guest tracking

**Files:**
- Create: `api/database/migrations/*_create_rentals_table.php`, `*_create_rental_items_table.php`, `*_create_rental_deposits_table.php`, `*_create_rental_returns_table.php`
- Create: `api/app/Models/Rental.php`, `RentalItem.php`, `RentalDeposit.php`, `RentalReturn.php`
- Create: `api/app/Http/Controllers/Api/V1/RentalController.php`
- Create: `api/app/Http/Requests/StoreRentalRequest.php`
- Create: `api/app/Services/RentalNumberGenerator.php`
- Test: `api/tests/Feature/RentalBookingTest.php`

**Interfaces:**
- Produces: `POST /api/v1/rentals` with `product_slug, from, to, customer, address?, deposit_mode?`.
- Produces: `Rental` with `number`, dates, policy snapshot, totals, status, payment_status.
- Produces: `GET /api/v1/rentals/{number}?phone=` guest lookup.
- Produces: deposit record with configured mode and status `pending`.

- [ ] **Step 1: Write the failing test**

```php
<?php
// api/tests/Feature/RentalBookingTest.php
it('creates a rental and reserves a unit atomically', function () {
    $p = \App\Models\Product::factory()->create(['slug' => 'khanjar', 'is_rentable' => true]);
    \App\Models\RentalUnit::factory()->create(['product_id' => $p->id]);
    \App\Models\RentalPolicy::factory()->create(['is_default' => true, 'deposit_mode' => 'charge']);

    $this->postJson('/api/v1/rentals', [
        'product_slug' => 'khanjar', 'from' => '2026-12-01', 'to' => '2026-12-03',
        'customer' => ['name' => 'Salim', 'phone' => '+96890000000', 'email' => 's@example.com'],
    ])->assertCreated()->assertJsonPath('data.status', 'pending');

    expect(\App\Models\RentalBooking::count())->toBe(1);
});
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `cd api; php artisan test --filter=RentalBookingTest`
Expected: FAIL.

- [ ] **Step 3: Create migrations and models**

`rentals`: number, user_id nullable, customer fields, start/end dates, status, policy_snapshot JSON, subtotal/deposit/total baisa, payment_status, notes. `rental_items`: rental_id, rental_unit_id, product_id, price_baisa. `rental_deposits`: rental_id, mode, amount_baisa, status, transaction_ref, released_at, notes. `rental_returns`: rental_id, returned_at, condition, damage_fee_baisa, late_fee_baisa, notes, inspected_by.

- [ ] **Step 4: Implement booking service in a transaction**

Lock available units, create rental/items/deposit, create `rental_bookings` rows, commit. Reject if no unit. Snapshot policy. Never touch sale stock.

- [ ] **Step 5: Implement guest lookup**

`GET /api/v1/rentals/{number}?phone=` returns 404 unless phone matches.

- [ ] **Step 6: Run the test to verify it passes**

Run: `cd api; php artisan test --filter=RentalBookingTest`
Expected: PASS.

- [ ] **Step 7: Commit**

```bash
git add api
git commit -m "feat: add rental booking, deposit records and guest lookup"
```

---

### Task 21: Rental checkout UI and confirmation

**Files:**
- Modify: `web/src/app/[locale]/rental/[slug]/page.tsx`
- Create: `web/src/app/[locale]/rentals/[number]/page.tsx`
- Create: `web/src/components/rental/RentalCheckoutForm.tsx`
- Test: `web/e2e/rental-checkout.spec.ts`

**Interfaces:**
- Produces: rental booking form (name, phone, email, ID upload if required, contract checkbox if required).
- Produces: confirmation page showing rental number, dates, deposit mode, total.
- Produces: guest rental tracking by number + phone.

- [ ] **Step 1: Write the failing test**

```ts
// web/e2e/rental-checkout.spec.ts
import { test, expect } from '@playwright/test';

test('books a rental and shows confirmation', async ({ page }) => {
  await page.goto('http://localhost:3000/en/rental');
  await page.getByTestId('rental-card').first().click();
  await page.getByTestId('rental-from').fill('2026-12-01');
  await page.getByTestId('rental-to').fill('2026-12-03');
  await page.getByLabel('Name').fill('Salim');
  await page.getByLabel('Phone').fill('+96890000000');
  await page.getByTestId('book-rental').click();
  await expect(page.getByTestId('rental-number')).toContainText('RNT-');
});
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `cd web; npx playwright test e2e/rental-checkout.spec.ts`
Expected: FAIL.

- [ ] **Step 3: Implement form and confirmation**

Show ID upload only when policy requires it. Show contract checkbox only when required. Submit `POST /api/v1/rentals`, redirect to `/rentals/{number}`. Confirmation shows deposit status as "pending" until admin records it.

- [ ] **Step 4: Run the test to verify it passes**

Run: `cd web; npx playwright test e2e/rental-checkout.spec.ts`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add web
git commit -m "feat: add rental checkout and confirmation"
```

---

### Task 22: Rental admin management, returns and deposit release

**Files:**
- Create: `api/app/Filament/Resources/RentalResource.php`
- Create: `api/app/Filament/Resources/RentalResource/Pages/ViewRental.php`
- Create: `api/app/Actions/RecordRentalReturn.php`, `ReleaseDeposit.php`
- Create: `api/tests/Feature/RentalReturnTest.php`

**Interfaces:**
- Produces: admin actions `markPickedUp`, `recordReturn`, `releaseDeposit`, `recordDamageCharge`.
- Produces: `Rental` status transitions: `pending → confirmed → picked_up → returned → completed` or `cancelled`.
- Produces: deposit status transitions per policy.

- [ ] **Step 1: Write the failing test**

```php
<?php
// api/tests/Feature/RentalReturnTest.php
it('records a return and releases the deposit', function () {
    $rental = \App\Models\Rental::factory()->create(['status' => 'picked_up', 'deposit_baisa' => 20000]);
    $deposit = \App\Models\RentalDeposit::factory()->create(['rental_id' => $rental->id, 'status' => 'charged']);

    app(\App\Actions\RecordRentalReturn::class)->execute($rental, ['condition' => 'good']);
    app(\App\Actions\ReleaseDeposit::class)->execute($rental);

    expect($rental->fresh()->status)->toBe('completed');
    expect($deposit->fresh()->status)->toBe('released');
});
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `cd api; php artisan test --filter=RentalReturnTest`
Expected: FAIL.

- [ ] **Step 3: Implement actions and admin UI**

Return action stores `rental_returns`, calculates late fee from policy, updates status. Release action marks deposit released/refunded based on mode; records partial deduction when damage fee exists. Admin view shows timeline, deposit, return form, and actions gated by `rental_manager`/`super_admin`.

- [ ] **Step 4: Run the test to verify it passes**

Run: `cd api; php artisan test --filter=RentalReturnTest`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add api
git commit -m "feat: add rental admin actions, returns and deposit release"
```

---

### Task 23: Payment adapters and idempotent webhook

**Files:**
- Create: `api/app/Services/Payments/PaymentManager.php`, `PaymentDriver.php`, `CodDriver.php`, `BankTransferDriver.php`, `GatewayDriver.php`
- Create: `api/app/Http/Controllers/Api/V1/PaymentWebhookController.php`
- Modify: `api/app/Services/Payments/PaymentManager.php` in Task 14 order flow
- Test: `api/tests/Feature/PaymentWebhookTest.php`

**Interfaces:**
- Produces: `PaymentManager::driver(string $method): PaymentDriver`.
- Produces: `PaymentDriver::create(Payment $payment): array` and `handleWebhook(Request $r): Payment`.
- Produces: `POST /api/v1/payments/webhook/{gateway}` idempotent by `transaction_ref`.

- [ ] **Step 1: Write the failing test**

```php
<?php
// api/tests/Feature/PaymentWebhookTest.php
it('does not double-process a duplicate webhook', function () {
    $order = \App\Models\Order::factory()->create(['payment_status' => 'pending']);
    $payment = \App\Models\Payment::factory()->create([
        'payable_type' => $order::class, 'payable_id' => $order->id,
        'gateway' => 'test', 'transaction_ref' => 'TX-1', 'status' => 'pending',
    ]);

    $payload = ['transaction_ref' => 'TX-1', 'status' => 'paid'];
    $this->postJson('/api/v1/payments/webhook/test', $payload)->assertOk();
    $this->postJson('/api/v1/payments/webhook/test', $payload)->assertOk();

    expect($payment->fresh()->status)->toBe('paid');
    expect(\App\Models\Payment::where('transaction_ref', 'TX-1')->count())->toBe(1);
});
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `cd api; php artisan test --filter=PaymentWebhookTest`
Expected: FAIL.

- [ ] **Step 3: Implement drivers and manager**

`CodDriver` marks payment pending; `BankTransferDriver` returns instructions; `GatewayDriver` is a stub that reads config and verifies signature. Manager resolves by method. Webhook finds payment by `transaction_ref`, returns early if already paid, updates status and order payment_status.

- [ ] **Step 4: Run the test to verify it passes**

Run: `cd api; php artisan test --filter=PaymentWebhookTest`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add api
git commit -m "feat: add payment adapters and idempotent webhook"
```

---

### Task 24: Shipping integration in checkout

**Files:**
- Modify: `api/app/Http/Controllers/Api/V1/OrderController.php`
- Create: `api/app/Http/Controllers/Api/V1/ShippingRateController.php`
- Modify: `web/src/app/[locale]/checkout/page.tsx`
- Test: `api/tests/Feature/ShippingRateTest.php`, `web/e2e/shipping.spec.ts`

**Interfaces:**
- Produces: `GET /api/v1/shipping/rates?governorate=Muscat&weight_grams=1200&subtotal_baisa=10000` → `{rate_baisa, free}`.
- Produces: checkout updates shipping total when governorate changes.
- Produces: order creation uses server-calculated shipping, never client-provided totals.

- [ ] **Step 1: Write the failing API test**

```php
<?php
// api/tests/Feature/ShippingRateTest.php
it('returns the rate for a governorate and weight', function () {
    $zone = \App\Models\ShippingZone::factory()->create(['governorate' => 'Muscat']);
    \App\Models\ShippingRate::factory()->create([
        'shipping_zone_id' => $zone->id, 'min_weight_grams' => 0,
        'max_weight_grams' => 5000, 'rate_baisa' => 2000,
    ]);

    $this->getJson('/api/v1/shipping/rates?governorate=Muscat&weight_grams=1200&subtotal_baisa=10000')
        ->assertOk()->assertJsonPath('data.rate_baisa', 2000);
});
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `cd api; php artisan test --filter=ShippingRateTest`
Expected: FAIL.

- [ ] **Step 3: Implement rate endpoint and server calculation**

Find active zone by governorate, matching weight band, apply free threshold. Order controller calls `ShippingCalculator` and ignores any client shipping total.

- [ ] **Step 4: Write the failing E2E test**

```ts
// web/e2e/shipping.spec.ts
import { test, expect } from '@playwright/test';

test('shows shipping when governorate is selected', async ({ page }) => {
  await page.goto('http://localhost:3000/en/checkout');
  await page.getByLabel('Governorate').selectOption('Muscat');
  await expect(page.getByTestId('shipping-total')).toContainText('OMR');
});
```

- [ ] **Step 5: Implement checkout shipping display**

Fetch rate when governorate changes; show shipping total and grand total. Disable place-order if no rate.

- [ ] **Step 6: Run tests to verify they pass**

Run: `cd api; php artisan test --filter=ShippingRateTest`, then `cd ..\web; npx playwright test e2e/shipping.spec.ts`
Expected: PASS.

- [ ] **Step 7: Commit**

```bash
git add .`r`ngit commit -m "feat: add Oman shipping rates and checkout integration"
```

---

### Task 25: Admin dashboard and reports

**Files:**
- Create: `api/app/Filament/Widgets/SalesOverview.php`, `RentalOverview.php`, `LowStockTable.php`
- Modify: `api/app/Providers/Filament/AdminPanelProvider.php`
- Test: `api/tests/Feature/AdminDashboardTest.php`

**Interfaces:**
- Produces: dashboard widgets for today's sales, open rentals, outstanding deposits, low stock.
- Produces: widgets respect role permissions.

- [ ] **Step 1: Write the failing test**

```php
<?php
// api/tests/Feature/AdminDashboardTest.php
it('loads the admin dashboard for staff', function () {
    $this->seed(\Database\Seeders\RoleSeeder::class);
    $admin = \App\Models\User::factory()->create();
    $admin->assignRole('super_admin');
    $this->actingAs($admin)->get('/admin')->assertOk();
});
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `cd api; php artisan test --filter=AdminDashboardTest`
Expected: FAIL.

- [ ] **Step 3: Implement widgets**

Use Filament stats overview and table widgets. Sales: sum `orders.total_baisa` where paid today. Rentals: count `rentals` where status in `confirmed,picked_up`. Deposits: sum `rental_deposits.amount_baisa` where status in `held,charged`. Low stock: variants `sale_stock <= 2`.

- [ ] **Step 4: Run the test to verify it passes**

Run: `cd api; php artisan test --filter=AdminDashboardTest`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add api
git commit -m "feat: add admin dashboard and reports"
```

---

### Task 26: Content pages, SEO and sitemap

**Files:**
- Create: `web/src/app/[locale]/pages/[slug]/page.tsx`
- Create: `web/src/app/sitemap.ts`, `web/src/app/robots.ts`
- Modify: `web/src/app/[locale]/layout.tsx`
- Test: `web/e2e/seo.spec.ts`

**Interfaces:**
- Produces: localized metadata (`title`, `description`, `alternates.languages`) for home, product, category, rental.
- Produces: `/sitemap.xml` includes localized product/category/rental URLs.
- Produces: CMS pages at `/{locale}/pages/{slug}`.

- [ ] **Step 1: Write the failing test**

```ts
// web/e2e/seo.spec.ts
import { test, expect } from '@playwright/test';

test('product page has localized metadata and hreflang', async ({ page }) => {
  await page.goto('http://localhost:3000/en/shop');
  await page.getByTestId('product-card').first().click();
  await expect(page.locator('link[rel="alternate"][hreflang="ar"]')).toHaveCount(1);
  await expect(page.locator('link[rel="alternate"][hreflang="en"]')).toHaveCount(1);
});
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `cd web; npx playwright test e2e/seo.spec.ts`
Expected: FAIL.

- [ ] **Step 3: Implement metadata, sitemap and CMS pages**

Use `generateMetadata` per route. Sitemap fetches product/category slugs from API. CMS page fetches `/api/v1/pages/{slug}`. Add `metadataBase` from env.

- [ ] **Step 4: Run the test to verify it passes**

Run: `cd web; npx playwright test e2e/seo.spec.ts`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add web
git commit -m "feat: add SEO metadata, sitemap and CMS pages"
```

---

### Task 27: E2E coverage for RTL, money, overlaps, guest isolation and payments

**Files:**
- Create: `web/e2e/rtl.spec.ts`, `web/e2e/money.spec.ts`, `web/e2e/rental-overlap.spec.ts`, `web/e2e/guest-isolation.spec.ts`
- Create: `api/tests/Feature/GuestIsolationTest.php`, `api/tests/Feature/RentalOverlapTest.php`
- Test: above files

**Interfaces:**
- Produces: regression coverage for the five Review Focus items.

- [ ] **Step 1: Write the RTL/money tests**

```ts
// web/e2e/rtl.spec.ts
import { test, expect } from '@playwright/test';
test('keeps OMR and phone readable in RTL', async ({ page }) => {
  await page.goto('http://localhost:3000/ar/cart');
  await expect(page.locator('[data-testid="cart-subtotal"]')).toContainText('ر.ع.');
  await expect(page.locator('[data-testid="cart-subtotal"]')).toHaveCSS('direction', 'rtl');
});
```

```ts
// web/e2e/money.spec.ts
import { test, expect } from '@playwright/test';
test('shows exactly 3 decimals', async ({ page }) => {
  await page.goto('http://localhost:3000/en/shop');
  await expect(page.getByTestId('price').first()).toHaveText(/OMR\s\d+\.\d{3}/);
});
```

- [ ] **Step 2: Write the guest isolation test**

```php
<?php
// api/tests/Feature/GuestIsolationTest.php
it('does not reveal an order with the wrong phone', function () {
    $order = \App\Models\Order::factory()->create(['number' => 'ORD-1', 'phone' => '+96890000000']);
    $this->getJson('/api/v1/orders/ORD-1?phone=+96891111111')->assertNotFound();
    $this->getJson('/api/v1/orders/ORD-1?phone=+96890000000')->assertOk();
});
```

- [ ] **Step 3: Write the rental overlap test**

```php
<?php
// api/tests/Feature/RentalOverlapTest.php
it('rejects an overlapping rental booking', function () {
    $p = \App\Models\Product::factory()->create(['slug' => 'khanjar', 'is_rentable' => true]);
    $u = \App\Models\RentalUnit::factory()->create(['product_id' => $p->id]);
    \App\Models\RentalPolicy::factory()->create(['is_default' => true, 'buffer_days' => 0]);
    \App\Models\RentalBooking::create(['rental_unit_id' => $u->id, 'start_date' => '2026-12-01', 'end_date' => '2026-12-03']);

    $this->postJson('/api/v1/rentals', [
        'product_slug' => 'khanjar', 'from' => '2026-12-02', 'to' => '2026-12-04',
        'customer' => ['name' => 'Salim', 'phone' => '+96890000000'],
    ])->assertUnprocessable();
});
```

- [ ] **Step 4: Run tests to verify they fail**

Run: `cd api; php artisan test --filter="GuestIsolationTest|RentalOverlapTest"`, then `cd ..\web; npx playwright test e2e/rtl.spec.ts e2e/money.spec.ts`
Expected: some FAIL.

- [ ] **Step 5: Fix failures**

Apply the smallest fix in the owning task's code. Do not weaken assertions. Re-run until green.

- [ ] **Step 6: Commit**

```bash
git add .`r`ngit commit -m "test: cover RTL, OMR precision, rental overlaps and guest isolation"
```

---

### Task 28: Local run scripts, seed data and deployment notes

**Files:**
- Create: `omani-shop/README.md`
- Create: `omani-shop/start-api.ps1`, `start-web.ps1`
- Create: `api/database/seeders/DemoCatalogSeeder.php`
- Modify: `api/.env.example`, `web/.env.example`
- Test: manual smoke checklist in README

**Interfaces:**
- Produces: one-command local start for API and web.
- Produces: demo data seeding 10 categories, 20 products, 5 rental units, default policy, shipping rates.
- Produces: deployment notes for moving to cloud later.

- [ ] **Step 1: Write the failing smoke check**

Run: `cd omani-shop; .\start-api.ps1` in one terminal and `.\start-web.ps1` in another; `GET http://localhost:8000/api/v1/health` returns ok; `http://localhost:3000/ar` loads.

- [ ] **Step 2: Implement scripts and README**

`start-api.ps1`: `cd api; php artisan serve --host=127.0.0.1 --port=8000`. `start-web.ps1`: `cd web; npm run dev`. README documents prerequisites (PHP 8.3+, Composer, Node 20+, MySQL 8), DB creation, migrations, seeders, admin login, and the local smoke checklist.

- [ ] **Step 3: Add demo seeder**

Seed categories (musar, shawls, khanjar, saif, bisht, asa, etc.), bilingual products, one rental policy, Oman shipping zones/rates, and a demo admin.

- [ ] **Step 4: Run migrations and seeders**

Run: `cd api; php artisan migrate:fresh --seed`
Expected: no errors.

- [ ] **Step 5: Run the smoke check**

Confirm health endpoint, Arabic home, English home, shop listing, rental page, admin login.

- [ ] **Step 6: Commit**

```bash
git add .`r`ngit commit -m "chore: add local run scripts, demo seed and deployment notes"
```

---

## Self-Review

**Spec coverage:** Intent (Tasks 1–28), goals (catalog 6–9, sell 11–15, rental 16–22, payments 14/23, shipping 10/24, admin 5/25, bilingual 2/3/7/26), non-goals respected (no international/multi-currency/marketplace), anti-goals (separate inventory 17, RTL tests 27, guest checkout 15), constraints (greenfield 1, OMR baisa 11, configurable policies 16/18/22, manual entry 7/8/28).

**Step scan:** Every step names a file, command, or assertion. No TBD steps.

**Type consistency:** `price_baisa`, `total_baisa`, `deposit_baisa`, `sale_stock`, `search_text`, `availableUnits`, `quote`, `formatOmr`, `apiGet` are used consistently.

**Review Focus:** Tests exist in Task 27 for RTL (rtl.spec), OMR precision (money.spec), rental overlap (RentalOverlapTest), guest isolation (GuestIsolationTest), and in Task 23 for webhook replay (PaymentWebhookTest).

**Proportion:** The plan is longer than the spec because it is a full-stack greenfield build; code blocks are limited to tests and signatures.

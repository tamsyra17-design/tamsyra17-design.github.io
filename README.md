# Omani Heritage Shop

A bilingual (Arabic/English, RTL) online shop for Omani heritage items
(musar, shawl, khanjar, saif, bisht, asa). Supports selling and renting, with
OMR pricing and Oman-only shipping.

Stack: Laravel 12 API (`api/`) + Next.js 16 storefront (`web/`) + MySQL/MariaDB.

## Prerequisites

- PHP 8.3+ with `openssl`, `mbstring`, `pdo_mysql`, `zip` (XAMPP ships these)
- Composer 2
- Node 20+
- MySQL 8 (or MariaDB 10.11 for local dev)
- Git

## First-time setup

```powershell
# 1. Database
mysql -u root -e "CREATE DATABASE omani_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 2. API
cd api
copy .env.example .env
# edit .env: DB_DATABASE=omani_shop, ADMIN_EMAIL, ADMIN_PASSWORD, APP_KEY (php artisan key:generate)
composer install
php artisan migrate --seed
php artisan storage:link

# 3. Web
cd ..\web
copy .env.example .env.local
npm install
```

## Running locally

Two terminals:

```powershell
.\start-api.ps1   # http://127.0.0.1:8000
.\start-web.ps1   # http://localhost:3000
```

## Smoke checklist

- [ ] `GET http://127.0.0.1:8000/api/v1/health` returns `{"status":"ok","database":"ok"}`.
- [ ] `http://localhost:3000/ar` loads in RTL with the Arabic home.
- [ ] `http://localhost:3000/en` loads in LTR with the English home.
- [ ] `http://localhost:3000/ar/shop` lists products with OMR prices.
- [ ] `http://localhost:3000/ar/rental` lists rentable items.
- [ ] `http://127.0.0.1:8000/admin` opens the Arabic Filament panel; log in with `ADMIN_EMAIL` / `ADMIN_PASSWORD`.
- [ ] A customer (no staff role) is refused from `/admin`.
- [ ] Guest checkout works end to end and the confirmation page shows the order number.
- [ ] Guest rental booking works and the confirmation page shows the rental number.

## Admin roles

`super_admin`, `catalog_manager`, `order_manager`, `rental_manager`,
`content_manager`, `support`. Only `super_admin` manages users and settings.

## Deploying to cloud later

- Set `APP_ENV=production`, `APP_DEBUG=false`, a real `APP_KEY`, and production DB credentials.
- Run `php artisan migrate --force` and `php artisan db:seed --class=DemoCatalogSeeder` (optional).
- Run `php artisan filament:assets` so the panel CSS/JS is published.
- Point `NEXT_PUBLIC_API_URL` at the production API and `NEXT_PUBLIC_SITE_URL` at the production web origin.
- Build the web: `npm run build` (needs ~2 GB free RAM on the build host).
- Put the API behind HTTPS; the web origin must be allowed by CORS.

## Money

All amounts are stored as integer **baisa** (1 OMR = 1000 baisa) and formatted
to 3 decimals. Never store or transmit floats.

## Notes

- Arabic is the default locale; English is opt-in.
- Shipping is Oman-only; governorates are the shipping zones.
- The 3D signature layer (docs/superpowers/specs/2026-11-16-3d-signature-layer-design.md)
  is a separate phase that lands after the commerce tasks.

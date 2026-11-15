# Design: Omani Heritage Online Shop (Sell + Rental)

**Date:** 2026-11-15
**Status:** Draft for approval
**Approach:** C — Next.js (frontend) + Laravel (API) + MySQL

## 1. Intent and why

Build an online shop for Omani heritage items (musar, shawls, khanjar, saif, bisht, asa, and similar), with two parallel business lines:

1. **Sell** — standard e-commerce checkout.
2. **Rental** — a separate rental experience for selected items (occasions, events, photoshoots, etc.).

The shop must be **bilingual (Arabic + English, RTL-aware)** and priced in **OMR**, shipping **within Oman only**. It must be **customizable from an admin panel** (products, categories, pricing, rental terms, content, settings) and support **role-based staff access**. Initial deployment target is **XAMPP/local**, with a path to cloud hosting later.

Primary goal: a trustworthy, culturally respectful storefront that lets customers buy or rent Omani heritage items with minimal friction, while giving the owner full control over catalog and operations.

## 2. Goals, non-goals, anti-goals

**Goals**
- Bilingual Arabic/English storefront with correct RTL layout.
- Catalog of ~2,000 SKUs across 20–25 categories.
- Sell flow: browse → cart → checkout → payment → order tracking.
- Rental flow: dedicated rental page → availability → date selection → deposit → confirmation.
- Multiple payment methods (online gateway + COD + bank transfer).
- Oman-only shipping with wilayat-based rates.
- Admin panel with role-based access (super admin, catalog, orders, rentals, content).
- Separate inventory pools for sale vs. rental.

**Non-goals (v1)**
- International shipping / multi-currency.
- Customer-to-customer marketplace or vendor self-service.
- Native mobile apps (responsive web first).
- Advanced ERP/accounting integration.

**Anti-goals**
- Do not couple rental availability to sale stock.
- Do not ship a UI that breaks RTL or mixes LTR/RTL text incorrectly.
- Do not require customers to create an account before browsing or adding to cart.

## 3. Constraints

- **Stack:** Next.js frontend + Laravel API + MySQL (chosen approach C).
- **Runtime (v1):** XAMPP/local (Apache/Nginx + MySQL + PHP). Node.js for Next.js dev/prod.
- **Languages:** Arabic (primary, RTL) + English.
- **Currency:** OMR (3 decimal places).
- **Shipping:** Oman only.
- **Scale:** ~2,000 products, 20–25 categories, moderate traffic.
- **Payments:** multiple methods; gateway credentials configurable in admin.
- **Greenfield:** built from scratch. No integration with, dependency on, or migration from the existing POS/rental system in this directory. Its catalog, stock, and rental data are unrelated.
- **Admin data entry:** products are entered manually through the admin panel. The admin must provide fast entry (duplicate/copy, defaults, inline media upload) and optional CSV import.
- **Configurable policies:** deposit, return, late-fee, damage-fee, ID/contract, and rental pricing rules are admin-configurable, not hard-coded.
- **Compliance:** Omani consumer expectations; clear rental terms, deposits, and return policy.

## 4. Approaches considered

| Approach | Summary | Why not chosen |
|---|---|---|
| A. Laravel + Blade/Tailwind | Server-rendered PHP, simpler ops | Less flexible modern UX; bilingual RTL and rich interactions harder to iterate |
| B. Plain PHP + MySQL | Fastest to start on XAMPP | Too much boilerplate for auth, i18n, admin, rentals at this scale |
| **C. Next.js + Laravel API** | Decoupled SPA/SSR frontend, robust API backend | More setup/ops complexity, but best fit for bilingual RTL UX and long-term growth |

**Chosen: C.** The decoupled model keeps the Arabic/English RTL experience first-class while Laravel handles auth, payments, inventory, and rentals robustly. XAMPP provides MySQL; Next.js runs via Node.

## 5. Architecture

### 5.1 High-level

```
[Next.js (SSR/CSR)]  <--HTTPS/JSON-->  [Laravel API]  <--->  [MySQL]
       |                                    |
   i18n/RTL UI                       Auth (Sanctum)
   Cart/Checkout                      Payments (adapters)
   Rental calendar                    Inventory (sale/rental)
                                      Admin (role-based)
                                      Shipping (Oman zones)
```

- Frontend and backend are separate deployable units; communicate via versioned REST API (`/api/v1`).
- MySQL is the single source of truth for catalog, orders, rentals, users.
- File/media storage: local disk in v1 (XAMPP `storage`), S3-compatible later.

### 5.2 Frontend (Next.js)

- Next.js App Router, TypeScript, Tailwind CSS, shadcn/ui.
- i18n via `next-intl`: Arabic (default, RTL) + English (LTR).
- State: React Query for server data; Zustand for cart/UI.
- Forms: React Hook Form + Zod.
- SEO: SSR/SSG for product/category pages, localized metadata, sitemap, robots.
- Images: Next/Image with responsive sizes; lazy loading.
- Search: site search in v1, tolerant of Arabic spelling variation (alef/hamza forms, ta marbuta vs ha, diacritics, tatweel). Start with a normalized MySQL full-text index; upgrade to Meilisearch if needed.
- Checkout: guest checkout allowed; order lookup by order number + phone. Account optional and can be created after purchase.

### 5.3 Backend (Laravel)

- Laravel 11/12, PHP 8.3+.
- Sanctum for customer + staff auth (token + cookie sessions).
- REST API resources with Form Request validation.
- Queues (database driver in v1) for emails/notifications.
- Localization via Laravel lang files + DB-backed content where needed.
- **Admin panel:** Laravel + Filament (Arabic/English, RTL-ready), with role- and permission-based access. One admin UI, sharing the API's models and policies.
- **Admin data entry:** fast product form with duplicate/copy, category defaults, drag-and-drop image upload, variant matrix, and optional CSV import.

### 5.4 Database (MySQL)

Core entities (high level):
- `users`, `roles`, `permissions`, `role_user`
- `categories`, `products`, `product_variants`, `product_images`, `attributes`, `attribute_values`
- `inventory_sale` (stock per variant), `inventory_rental` (rentable units)
- `carts`, `cart_items`
- `orders`, `order_items`, `payments`, `shipments`, `addresses`
- `rentals`, `rental_items`, `rental_deposits`, `rental_returns`
- `rental_policies` (configurable pricing, deposit, return, late/damage rules)
- `shipping_zones`, `shipping_rates`
- `settings`, `pages` (CMS), `translations` (if DB-backed)
- `audit_logs`

**Product model:** one product record can be sold and/or rented. It has `is_rentable`, separate sale stock and rental units, an optional sale price, and rental pricing. Categories may be sale-only, rental-only, or both.

## 6. Key flows

### 6.1 Sell flow
1. Browse categories/products (localized).
2. Add to cart (guest allowed).
3. Checkout: address (Oman wilayat), shipping method, payment method.
4. Payment: gateway redirect / COD / bank transfer instructions.
5. Order confirmation + email/SMS; tracking via account or order number + phone (guest).

### 6.2 Rental flow
1. Dedicated `/rental` page lists rentable items.
2. Product detail shows availability calendar and per-day/week pricing.
3. Customer selects date range; system checks availability (no overlap).
4. Rental fee + deposit calculated by the active policy; customer pays per the configured deposit mode.
5. Admin marks picked up / returned; deposit released/refunded per the configured return policy.

### 6.3 Admin flow
- Role-based dashboard.
- CRUD products/categories/variants/images, with duplicate/copy and optional CSV import.
- Manage inventory (sale stock, rental units, blocked dates).
- Manage orders/rentals, update statuses, record deposits/refunds/damage charges.
- Configure payments, shipping rates, taxes/fees, content, settings.
- Configure rental policy: pricing rule, deposit method, return inspection window, late/damage fees, ID upload and contract acceptance requirements.
- Reports: sales, rentals, top products, low stock, outstanding deposits.

## 7. Internationalization & RTL

- Default locale: Arabic (RTL). Secondary: English (LTR).
- Locale prefix in URL (`/ar`, `/en`) or subdomain; default Arabic.
- Tailwind RTL utilities; `dir="rtl"` on `<html>` for Arabic.
- All product/category/content fields stored with translations (JSON or related table).
- Number/currency formatting OMR with 3 decimals; dates in both calendars where relevant.
- Emails/SMS localized.

## 8. Payments

- Payment adapter interface; implementations: COD, bank transfer, online gateway (PayTabs/Tap/Thawani/Stripe where available).
- Gateway credentials and enabled methods configurable in admin (encrypted at rest).
- Webhook handling for payment status; idempotent processing.
- Refunds for cancellations/returns.
- **Rental deposit modes (configurable):** gateway authorization hold, charge-now-refund-later, cash at pickup, or bank transfer. If the chosen mode cannot hold funds, the system records the deposit as charged and tracks its release/refund.
- Deposit release timing, partial deductions, and damage charges are configurable per rental policy.

## 9. Shipping (Oman only)

- Address requires wilayat/governorate; validate Oman locations.
- Shipping rates by zone/weight; free shipping threshold configurable.
- Courier integration optional (manual status updates in v1).
- No international shipping in v1.

## 10. Inventory & rental availability

- Sale stock: quantity per variant; decrement on order payment; low-stock alerts.
- Rental units: individual trackable units (serial/asset) or quantity pools with date-based reservations.
- Rental availability check prevents overlapping bookings.
- Admin can block dates (maintenance/cleaning).
- **Rental pricing is configurable** per product/category/policy: base days + base fee + extra daily rate (default), optional weekly rate, inclusive/exclusive return day, minimum/maximum duration, and turnaround buffer between bookings.
- **Return settlement is configurable:** who inspects, when the deposit is released, late-fee rule, damage-fee schedule or free-text charge, and whether ID upload and contract acceptance are required.
- Rental-only categories and products are supported.

## 11. Security & roles

- Roles: `super_admin`, `catalog_manager`, `order_manager`, `rental_manager`, `content_manager`, `support`.
- Permissions granular per module/action.
- Customer accounts separate from staff.
- Rate limiting, CSRF/CORS, input validation, encrypted secrets.
- Audit log for admin actions.

## 12. Testing & quality

- Backend: PHPUnit/Pest for units/feature tests; API contract tests.
- Frontend: Playwright for E2E (bilingual, RTL, checkout, rental).
- Linting: ESLint/Prettier, PHP CS Fixer/Pint.
- CI optional (GitHub Actions) later.

## 13. Deployment (v1)

- **Local:** XAMPP MySQL + `php artisan serve` (API) + `next dev` (frontend).
- Environment via `.env` files; seeded demo data.
- Later: cloud (VPS/Docker) with Nginx, queue worker, scheduled tasks.

## 14. Open items for builder

- Final brand name and logo (placeholder: "Omani Heritage").
- Exact payment gateways available in Oman (configure adapters accordingly).
- Shipping courier choice (manual in v1).
- SMS provider for Oman (optional).
- Tax/VAT rules in Oman (configure as settings).

## 15. Decisions made by the builder (check these)

- **Admin:** Laravel + Filament, bilingual (Arabic/English, RTL).
- **Search:** included in v1, Arabic-tolerant; MySQL full-text first.
- **Guest checkout:** allowed; order/rental lookup by number + phone.
- **Product model:** one product can be sold and/or rented; separate inventory.
- **Rental policy:** all pricing/deposit/return/late/damage/ID rules are admin-configurable, with base-days + base-fee + extra-daily as the default rule.
- **Admin data entry:** manual entry with duplicate/copy/defaults/media upload; optional CSV import.
- **Greenfield:** no POS integration or migration.

## 16. What's left to you (human)

- Approve this design (or request changes).
- Provide brand assets (logo, colors) when ready.
- Provide payment gateway credentials at integration time.

---

**Next step after approval:** invoke `writing-plans` to produce an implementation plan.

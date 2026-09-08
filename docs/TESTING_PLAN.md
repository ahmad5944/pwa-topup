# Testing Plan

> STATUS: ✅ Implemented. `php artisan test` → 60 passed, 7 pre-existing skipped (disabled Jetstream features), 0 failed.

## Critical bug found & fixed during this effort
`routes/web.php` had `Route::middleware('role:admin,cs')`. Spatie's `RoleMiddleware` only splits
roles on `|` (see `vendor/spatie/laravel-permission/src/Middleware/RoleMiddleware.php`), so this
was checking for a literal role named `"admin,cs"` which never exists — **every admin and cs user
was getting 403 Forbidden on all `/admin/*` routes**. Fixed to `role:admin|cs`.

## Test database setup
The app uses Postgres-only features (`jsonb` columns, `ilike` queries in Catalog/Admin
controllers), so SQLite is not a safe test driver here. Instead:
- `.env.testing` — same Postgres credentials as `.env`, but `DB_DATABASE=topup_pwa_test` (separate
  from the dev database so `RefreshDatabase` never wipes the seeded dummy data), plus dummy
  `MIDTRANS_SERVER_KEY`/`MIDTRANS_CLIENT_KEY` so `MidtransService` doesn't blow up on null config
  when a test happens to reach it.
- One-time setup required: `CREATE DATABASE topup_pwa_test;` in Postgres.

## Test files
- `tests/Unit/ProductPriceForLevelTest.php` — pure math test for `Product::priceForLevel()`
  (base price, percentage markup, rounding), no DB.
- `tests/Feature/Actions/PlaceOrderActionTest.php` — balance deduction, insufficient-balance
  exception, price-level markup applied, `Transaction` + `TransactionLog` created,
  `ProcessTopupOrderJob` dispatched (`Queue::fake()` in every test method to avoid real
  Digiflazz calls via the sync queue driver).
- `tests/Feature/Actions/CreditBalanceActionTest.php` — credits balance + marks deposit
  successful; idempotent (no double-credit on an already-successful deposit).
- `tests/Feature/Actions/ApplyProviderResultActionTest.php` — "Sukses" marks the transaction
  successful and credits the upline reseller's commission (margin = price_jual - price_beli);
  "Gagal" marks failed and refunds the user's balance.
- `tests/Feature/CatalogControllerTest.php` — guest redirected to login; only active products
  shown; category slug filter; inactive product detail returns 404.
- `tests/Feature/OrderControllerTest.php` — happy path; `target_number` validation; insufficient
  balance returns a validation error instead of a 500; guests blocked.
- `tests/Feature/TransactionControllerTest.php` — IDOR: owner can view, another user gets 403;
  index only lists the authenticated user's own transactions.
- `tests/Feature/FavoriteProductControllerTest.php` — store; owner can delete; non-owner gets
  403 (IDOR).
- `tests/Feature/AppNotificationControllerTest.php` — owner can mark as read; non-owner gets
  403 (IDOR).
- `tests/Feature/DepositControllerTest.php` — amount below 10000 rejected (422); success path
  mocks the Midtrans Snap endpoint via `Http::fake()` (never hits the real network).
- `tests/Feature/AdminAccessTest.php` — role gating (member 403 on `/admin/*` and
  `/reseller/*`; admin/cs 200 on admin routes; reseller 200 on reseller routes); confirms
  `Provider.api_key`/`api_secret` never appear in the response body.
- `tests/Feature/DashboardControllerTest.php` — member/reseller get the `Dashboard` Inertia
  component; admin/cs get `Admin/Dashboard`.

## Conventions
- Feature tests use `RefreshDatabase` + `actingAs()`.
- Role-dependent tests seed `RoleSeeder` in `setUp()`.
- Inertia responses asserted via `assertInertia()` (`Inertia\Testing\AssertableInertia`, bundled
  with `inertiajs/inertia-laravel`) to check component name + prop shape.
- Any test path that reaches an outbound HTTP call (Digiflazz, Midtrans) either fakes the queue
  (`Queue::fake()`) or fakes the HTTP client (`Http::fake()`) — tests must never hit real
  third-party APIs.

## Running the suite
```
php artisan test
```

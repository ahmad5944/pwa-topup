# Frontend Rebuild Plan — Dummy Seeders + Blue/Dark PWA Theme

> STATUS: ✅ Implemented (all 7 phases). This is a historical record of the execution plan used to build the full Member/Reseller/Admin frontend, dummy data seeders, and blue/dark PWA theme. See [PLANNING.md](PLANNING.md) for the living roadmap and current checklist status.

Scope: Member + Reseller + Admin frontend all at once, primary color Tailwind blue-600, dark mode via class strategy, plus basic PWA installability (manifest.webmanifest + icons). Full offline service worker/caching was deliberately deferred (not requested, avoid over-engineering).

## Phase 0 — Design System Foundation
- `tailwind.config.js`: `darkMode: 'class'`, `colors.primary` = blue scale (50-950).
- `resources/css/app.css`: dark-mode base styles, smooth color-scheme transition.
- `resources/js/Composables/useDarkMode.js`: reads `localStorage.theme` → falls back to `prefers-color-scheme`.
- PWA installability: `public/manifest.webmanifest`, icon, meta tags + no-FOUC script in `resources/views/app.blade.php`.

## Phase 1 — Dummy Data
- New factories: `CategoryFactory`, `ProviderFactory`, `PriceLevelFactory`, `ProductFactory`, `TransactionFactory`, `TransactionLogFactory`, `DepositFactory`, `CommissionFactory`, `ResellerDownlineFactory`, `AppNotificationFactory`, `FavoriteProductFactory`. Extended `UserFactory` with `balance`, `phone`, `price_level_id`.
- `database/seeders/DummyDataSeeder.php`: 3 price levels, 3 providers, 6 categories, ~60 products, ~22 users across all 4 roles, ~150 transactions + logs, deposits, commissions, favorites, notifications — all seeded directly (no dispatching jobs / no real Digiflazz/Midtrans calls).

## Phase 2 — Backend Endpoints
- New controllers: `DashboardController` (branches by role), `CatalogController`, `OrderController`, `TransactionController`, `FavoriteProductController`, `AppNotificationController`, `Reseller/DashboardController`, `Reseller/DownlineController`, `Reseller/CommissionController`, `Admin/ProviderController`, `Admin/ProductController`, `Admin/UserController`, `Admin/DepositController`.
- `routes/web.php` rewritten with role-gated groups (`role:reseller`, `role:admin|cs`).
- `HandleInertiaRequests` shares `userRoles`, `unreadNotificationsCount`, `midtrans.clientKey/isProduction`.
- IDOR guards on transactions/favorites/notifications (users can only see/modify their own records).

## Phase 3 — Shared Layouts & Components
- `Layouts/CustomerLayout.vue` — mobile-first, blue gradient header, bottom nav, dark-mode toggle, notification bell.
- `Layouts/AdminLayout.vue` — sidebar nav, topbar with dark-mode toggle.
- Components: `Catalog/ProductCard.vue`, `Catalog/CategoryTabs.vue`, `Wallet/BalanceBadge.vue`, `Notifications/NotificationBell.vue`, `UI/DarkModeToggle.vue`, `UI/StatusBadge.vue`, `UI/EmptyState.vue`.
- Composables: `usePolling.js`, `useCurrency.js`.

## Phase 4 — Member/Customer Pages
`Dashboard.vue` (redesigned), `Catalog/Index.vue` + `Catalog/Show.vue`, `Transactions/Index.vue` + `Show.vue` (with polling), `Deposits/Index.vue` + `Create.vue` (Midtrans Snap.js), `Favorites/Index.vue`, `Notifications/Index.vue`.

## Phase 5 — Reseller Pages
`Reseller/Dashboard.vue`, `Reseller/Downlines.vue`, `Reseller/Commissions.vue`.

## Phase 6 — Admin Pages
`Admin/Dashboard.vue`, `Admin/Providers/Index.vue`, `Admin/Products/Index.vue`, `Admin/Users/Index.vue`, `Admin/Deposits/Index.vue`.

## Phase 7 — Polish & Verification
Verified via `migrate:fresh --seed`, `npm run build` (834 modules, no errors), `route:list`, tinker smoke tests, and `vendor/bin/pint`.

## Key decisions
- Dummy seeder never dispatches jobs or calls real Digiflazz/Midtrans APIs.
- Service worker/offline caching intentionally excluded from this pass (manifest + icons only for now).
- Admin/reseller gated by dedicated routes + spatie `role:` middleware, not just hidden in the UI.
- Color system: Tailwind `blue` as `primary`, `darkMode: 'class'` (manual toggle + system-preference default).

## Known follow-ups
1. Manifest icon is a placeholder SVG "T" logomark — swap in real brand assets when available.
2. Midtrans Snap.js checkout needs real sandbox/production credentials in `.env` to fully test end-to-end.

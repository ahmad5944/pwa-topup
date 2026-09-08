# Planning: Frontend & Backend Integration — Topup PWA

> Dibuat berdasarkan audit kode per 2026-09-08. Dokumen ini adalah rencana kerja (living document) — update checklist saat progres berjalan.

## 1. Kondisi Saat Ini (Audit)

**Backend** sudah cukup matang untuk domain topup:
- Models: `User` (balance, price_level), `Provider`, `Product`, `Category`, `PriceLevel`, `Transaction`, `TransactionLog`, `Deposit`, `Commission`, `ResellerDownline`, `FavoriteProduct`, `AppNotification`, `PushToken`.
- Business logic: `PlaceOrderAction`, `ApplyProviderResultAction`, `CreditBalanceAction`, `ProviderRouter`, `DigiflazzProvider`, `MidtransService`.
- Jobs: `ProcessTopupOrderJob`, `ReconcilePendingTransactionsJob`, `ReconcileDepositsJob`, `SyncProviderPricesJob`.
- Roles (spatie/permission, lihat `database/seeders/RoleSeeder.php`): `admin`, `cs`, `reseller`, `member`.
- Webhooks untuk Digiflazz & Midtrans sudah ada dan terverifikasi (HMAC/signature).

**Frontend** baru berisi scaffold bawaan Laravel Jetstream:
- Halaman: `Welcome`, `Dashboard` (cuma render komponen `Welcome` placeholder), `Auth/*`, `Profile/*`, `API/*`.
- Tidak ada halaman untuk fitur inti: katalog produk, checkout/order, riwayat transaksi, deposit, favorit, notifikasi, reseller, admin.
- **Bug aktif**: `DepositController@index` me-render `Inertia::render('Deposits/Index', ...)` tapi folder `resources/js/Pages/Deposits/` tidak ada → route `/deposits` akan 500.
- Tidak ada controller/route untuk: katalog, order/checkout, riwayat transaksi, favorit, notifikasi, reseller, admin panel.

**PWA**: nama proyek "topup-pwa" tapi belum ada implementasi PWA sungguhan — tidak ada `vite-plugin-pwa`, tidak ada `manifest.webmanifest`, tidak ada service worker/offline fallback, meskipun `PushToken` model & `kreait/laravel-firebase` sudah terpasang (mengindikasikan rencana push notification).

## 2. Tujuan

1. Bangun frontend Vue 3 + Inertia yang lengkap untuk 3 peran: **member (customer)**, **reseller**, **admin/cs**.
2. Tambahkan seluruh controller/route/request/policy backend yang hilang agar frontend punya data nyata.
3. Jadikan aplikasi installable PWA (manifest + service worker + offline fallback + push notification).

## 3. Konvensi & Arsitektur

- **Routing**: pisahkan `routes/web.php` per grup middleware — `auth` umum, `role:reseller`, `role:admin|cs` (pakai `Route::middleware('role:admin,cs')->prefix('admin')->name('admin.')->group(...)`).
- **Struktur halaman**: setiap grup fitur dapat sub-folder di `Pages/` sesuai controller (`Inertia::render('Catalog/Index')` → `Pages/Catalog/Index.vue`).
- **Layout ganda**:
  - `AppLayout.vue` (sudah ada, dari Jetstream) tetap dipakai untuk Profile/API token.
  - `CustomerLayout.vue` (baru) — mobile-first, bottom navigation (Beranda/Transaksi/Deposit/Akun), dipakai member & reseller.
  - `AdminLayout.vue` (baru) — sidebar, dipakai admin/cs.
- **State/data**: props Inertia untuk data halaman; buat composable kecil (`resources/js/Composables/`) untuk polling status transaksi/deposit (`usePolling.js`), dan format uang (`useCurrency.js`). Tidak perlu Pinia/Vuex kecuali kompleksitas naik (misal keranjang multi-item).
- **Keamanan data**: jangan pernah kirim `api_key`/`api_secret`/`webhook_secret` provider ke props Inertia. Gunakan Eloquent `$hidden` atau API Resource eksplisit untuk `Provider` saat dipakai di halaman admin.
- **Shared props** (`HandleInertiaRequests`): tambahkan `auth.user.roles`, `auth.user.balance`, dan unread notification count agar bisa dipakai di semua layout tanpa query berulang.

## 4. Peta Modul: Halaman ↔ Route ↔ Controller

| Modul | Halaman Frontend | Route | Controller (backend) | Status |
|---|---|---|---|---|
| Auth/Profile | `Auth/*`, `Profile/*` | bawaan Fortify/Jetstream | bawaan | ✅ Ada |
| Dashboard | `Dashboard.vue` (redesain) | `GET /dashboard` | `DashboardController` (baru) | ⚠️ Perlu redesain + controller data nyata |
| Katalog | `Catalog/Index.vue`, `Catalog/Show.vue` | `GET /catalog`, `GET /catalog/{product}` | `CatalogController` (baru) | ❌ Belum ada |
| Order/Checkout | (modal/halaman di `Catalog/Show.vue`) | `POST /orders` | `OrderController@store` (baru, pakai `PlaceOrderAction`) | ❌ Belum ada |
| Riwayat Transaksi | `Transactions/Index.vue`, `Show.vue` | `GET /transactions`, `GET /transactions/{transaction}` | `TransactionController` (baru) | ❌ Belum ada |
| Deposit | `Deposits/Index.vue`, `Create.vue` | `GET/POST /deposits` | `DepositController` (sudah ada, halaman belum) | 🐛 Bug — halaman hilang |
| Favorit | `Favorites/Index.vue` | `GET/POST/DELETE /favorites` | `FavoriteProductController` (baru) | ❌ Belum ada |
| Notifikasi | `Notifications/Index.vue` + `NotificationBell.vue` | `GET /notifications`, `PATCH /notifications/{id}/read` | `AppNotificationController` (baru) | ❌ Belum ada |
| Reseller | `Reseller/Dashboard.vue`, `Downlines.vue`, `Commissions.vue` | `GET /reseller/*` (middleware `role:reseller`) | `Reseller\*Controller` (baru) | ❌ Belum ada |
| Admin — Providers | `Admin/Providers/Index.vue` | `GET/PATCH /admin/providers` | `Admin\ProviderController` (baru) | ❌ Belum ada |
| Admin — Produk | `Admin/Products/Index.vue` | `GET/PATCH /admin/products` | `Admin\ProductController` (baru) | ❌ Belum ada |
| Admin — Users | `Admin/Users/Index.vue` | `GET/PATCH /admin/users` | `Admin\UserController` (baru) | ❌ Belum ada |
| Admin — Deposit Approval | `Admin/Deposits/Index.vue` | `GET/PATCH /admin/deposits` | `Admin\DepositController` (baru) | ❌ Belum ada |
| Webhooks | — (tidak ada UI) | `POST /webhooks/*` | sudah ada | ✅ Ada |

## 5. Roadmap Bertahap

### Fase 0 — Hotfix (prioritas tertinggi)
- [ ] Buat `Pages/Deposits/Index.vue` (+ `Create.vue`/form) agar route `/deposits` tidak 500.
- [ ] Redesain `Dashboard.vue`: tampilkan saldo, produk favorit, transaksi terakhir, quick action (topup, deposit). Butuh `DashboardController` baru yang mengganti closure route di `routes/web.php`.

### Fase 1 — Customer Core (MVP beli produk)
- Backend: `CatalogController@index/show` (list kategori+produk aktif, filter per kategori), `OrderController@store/show` (pakai `PlaceOrderAction`, validasi `target_number`, rate limit).
- Frontend: `Catalog/Index.vue` (grid produk + tab kategori), `Catalog/Show.vue` (form input nomor tujuan + konfirmasi harga sesuai `priceForLevel`), `Transactions/Index.vue` & `Show.vue` (polling status via `usePolling`).
- Komponen baru: `ProductCard.vue`, `CategoryTabs.vue`, `BalanceBadge.vue`.

### Fase 2 — Deposit & Wallet
- Perbaiki halaman Deposit (Fase 0) + integrasi Midtrans Snap.js di frontend (`<script src="https://app.midtrans.com/snap/snap.js">`, buka popup pakai `snap_token` dari response `DepositController@store`).
- Riwayat status deposit (pending/success/failed) + auto-refresh setelah callback.

### Fase 3 — Notifikasi & Favorit
- `AppNotificationController` (index, mark-as-read) + `FavoriteProductController` (toggle favorite).
- `NotificationBell.vue` di layout (badge unread count dari shared props).

### Fase 4 — Reseller
- Middleware `role:reseller` pada grup route `/reseller/*`.
- Halaman: daftar downline (`ResellerDownline`), riwayat komisi (`Commission`), info price level aktif.

### Fase 5 — Admin Panel (`role:admin,cs`)
- CRUD/monitor Provider (aktif/nonaktif, priority, trigger manual `SyncProviderPricesJob`).
- Kelola Produk (override `price_jual`, aktif/nonaktif per produk).
- Kelola Users (ubah `price_level_id`, credit balance manual via `CreditBalanceAction`).
- Approval deposit manual/transfer (khusus method non-Midtrans).
- Laporan ringkas: omzet, transaksi sukses vs gagal.

### Fase 6 — PWA Hardening
- Install `vite-plugin-pwa`, generate `manifest.webmanifest` + icon set (192/512/maskable).
- Service worker: cache-first untuk asset statis, network-first untuk halaman/API, offline fallback page.
- Hubungkan `PushToken` model + Firebase (`kreait/laravel-firebase`) ke Web Push (izin notifikasi browser, simpan token saat login).
- Komponen "Add to Home Screen" prompt.

### Fase 7 — QA & Hardening Keamanan ✅
- Feature test (`tests/Feature`) untuk tiap controller baru — done: `PlaceOrderActionTest`, `CreditBalanceActionTest`, `ApplyProviderResultActionTest`, `CatalogControllerTest`, `OrderControllerTest`, `TransactionControllerTest`, `FavoriteProductControllerTest`, `AppNotificationControllerTest`, `DepositControllerTest`, `AdminAccessTest`, `DashboardControllerTest`, plus `Unit/ProductPriceForLevelTest`. Dedicated `topup_pwa_test` Postgres DB + `.env.testing` added so tests never touch the dev dummy data.
- Rate limiting di `POST /orders` — sudah ada (`throttle:10,1`, sama seperti deposits).
- Pastikan setiap route admin/reseller punya middleware role + tidak ada IDOR — done, covered by `AdminAccessTest` + IDOR tests on transactions/favorites/notifications. **Bug fixed**: `role:admin,cs` was invalid (Spatie's RoleMiddleware only splits on `|`, not `,`) and was silently 403-ing every admin/cs user — corrected to `role:admin|cs`.
- Audit: `Provider.api_key/api_secret/webhook_secret` never serialized to Inertia props — verified by `AdminAccessTest::test_provider_secrets_are_never_exposed_to_the_frontend`.

## 6. Struktur Folder Frontend (Target)

```
resources/js/
  Pages/
    Dashboard.vue
    Catalog/{Index,Show}.vue
    Transactions/{Index,Show}.vue
    Deposits/{Index,Create}.vue
    Favorites/Index.vue
    Notifications/Index.vue
    Reseller/{Dashboard,Downlines,Commissions}.vue
    Admin/Providers/Index.vue
    Admin/Products/Index.vue
    Admin/Users/Index.vue
    Admin/Deposits/Index.vue
    Auth/ Profile/ API/  (tetap, bawaan Jetstream)
  Layouts/
    AppLayout.vue        (bawaan, untuk Profile/API)
    CustomerLayout.vue   (baru, bottom-nav mobile)
    AdminLayout.vue       (baru, sidebar)
  Components/
    Catalog/ProductCard.vue
    Catalog/CategoryTabs.vue
    Wallet/BalanceBadge.vue
    Notifications/NotificationBell.vue
    ...(komponen Jetstream existing tetap dipakai)
  Composables/
    usePolling.js
    useCurrency.js
```

## 7. Catatan Keamanan (OWASP-relevan)

- Validasi `target_number` di `OrderController` (format angka, panjang, whitelist prefix operator jika perlu) sebelum diteruskan ke provider.
- Row lock (`lockForUpdate`) sudah dipakai di `PlaceOrderAction` untuk cegah double-spend — pertahankan pola ini di action baru yang mengubah balance.
- Semua route admin/reseller wajib middleware `role:` (spatie/permission), jangan hanya disembunyikan di UI.
- Jangan expose secret provider ke response JSON/Inertia manapun.
- Webhook signature verification (sudah ada di `DigiflazzWebhookController`/`MidtransWebhookController`) — jangan dihapus/dilemahkan saat refactor.

## 8. Checklist Ringkas

- [x] Fase 0: fix halaman Deposits + redesain Dashboard
- [x] Fase 1: Catalog + Order + Transactions
- [x] Fase 2: Deposit + Midtrans Snap frontend
- [x] Fase 3: Notifikasi + Favorit
- [x] Fase 4: Reseller
- [x] Fase 5: Admin Panel
- [x] Fase 6: PWA — manifest + icons + dark mode done; service worker/offline caching masih ditunda (belum diminta)
- [x] Fase 7: QA & security hardening — test suite + role-middleware bug fix

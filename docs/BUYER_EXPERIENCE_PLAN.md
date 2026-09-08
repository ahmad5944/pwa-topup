# Plan: Tampilan Pembeli (Member) — Audit & Roadmap Pengembangan

> Dokumen ini fokus khusus pada sisi **pembeli/customer (role `member`)** — bagian aplikasi yang paling sering dipakai end-user. Melengkapi [PLANNING.md](PLANNING.md) yang mencakup seluruh aplikasi (member+reseller+admin).

## 1. Audit — Tampilan Pembeli yang Sudah Ada

| Halaman | Route | File | Fitur |
|---|---|---|---|
| Dashboard | `GET /dashboard` | `Pages/Dashboard.vue` | Kartu saldo, 4 quick action, 5 transaksi terakhir, produk favorit |
| Katalog | `GET /catalog` | `Pages/Catalog/Index.vue` | Tab kategori, search, grid produk, pagination |
| Detail Produk & Order | `GET /catalog/{product}`, `POST /orders` | `Pages/Catalog/Show.vue` | Detail harga (sesuai price level), form nomor tujuan, submit order |
| Riwayat Transaksi | `GET /transactions` | `Pages/Transactions/Index.vue` | Filter status, list + badge status, pagination |
| Detail Transaksi | `GET /transactions/{id}` | `Pages/Transactions/Show.vue` | Detail invoice, auto-polling status tiap 4 detik selama belum final, log riwayat |
| Deposit | `GET /deposits`, `GET /deposits/create`, `POST /deposits` | `Pages/Deposits/Index.vue`, `Create.vue` | Riwayat deposit, preset nominal, integrasi Midtrans Snap.js |
| Favorit | `GET /favorites` | `Pages/Favorites/Index.vue` | List favorit, hapus favorit, shortcut beli |
| Notifikasi | `GET /notifications` | `Pages/Notifications/Index.vue` | List notifikasi, tandai dibaca |

**Layout**: `Layouts/CustomerLayout.vue` — header gradient biru + bottom navigation 5 menu (Beranda/Katalog/Transaksi/Deposit/Akun), dark mode toggle, badge notifikasi belum dibaca.

**Komponen pendukung**: `ProductCard`, `CategoryTabs`, `BalanceBadge`, `NotificationBell`, `StatusBadge`, `EmptyState`, `DarkModeToggle`.

**Gap yang teridentifikasi**:
- Tidak ada halaman "Akun/Profil" khusus mobile-first — menu "Akun" di bottom nav mengarah ke halaman Profile bawaan Jetstream (`AppLayout`, desktop-style, bukan `CustomerLayout`) sehingga terasa tidak konsisten temanya.
- Belum ada pencarian produk dengan debounce (submit hanya via Enter) — kurang responsif.
- Belum ada state loading/skeleton saat pindah halaman (mengandalkan Inertia default progress bar biru saja).
- Order hanya 1 produk per transaksi — tidak ada keranjang/bulk order.
- Tidak ada cara menyimpan nomor tujuan favorit terpisah dari produk favorit (field `target_number` di `favorite_products` ada di DB tapi belum dipakai di form order sebagai shortcut/autofill).
- Tidak ada riwayat "produk sering dibeli" untuk mempercepat re-order.

## 2. Roadmap Pengembangan Baru

### Fase A — Konsistensi & Polish UX (prioritas tinggi, effort kecil)
- [ ] Buat `Pages/Profile/Show.vue` versi `CustomerLayout` (atau bungkus halaman Jetstream Profile dengan header/bottom-nav yang konsisten) supaya menu "Akun" tidak berpindah gaya.
- [ ] Tambah debounce search di `Catalog/Index.vue` (300ms, tanpa perlu tekan Enter) pakai `watch` + `setTimeout`.
- [ ] Tambah skeleton loader sederhana (placeholder card pulsing) saat `router.visit` sedang loading, pakai Inertia `onStart`/`onFinish` event.
- [ ] Tambah toast/snackbar global untuk flash message (`success`/`error` dari session) alih-alih mengandalkan tampilan default per-halaman.

### Fase B — Pengalaman Order yang Lebih Cepat
- [ ] Autofill nomor tujuan dari `favorite_products.target_number` saat user membuka `Catalog/Show.vue` untuk produk yang sama (dropdown "nomor tersimpan").
- [ ] Tombol "Beli Lagi" di `Transactions/Index.vue` yang langsung mengisi ulang form order dengan produk & nomor yang sama.
- [ ] (Opsional, perlu perubahan skema) Dukungan multi-item/keranjang — saat ini `PlaceOrderAction` hanya menangani 1 produk per transaksi; kalau mau bulk order perlu desain ulang tabel `transactions` atau tabel `order_items` terpisah — **butuh diskusi lebih lanjut sebelum dikerjakan**, karena berdampak ke skema DB & action inti.

### Fase C — Engagement
- [ ] Banner promo di atas Dashboard (bisa hardcode dulu, atau tabel `promotions` baru dikelola admin).
- [ ] "Produk sering dibeli" di Dashboard (query `Transaction` group by `product_id` untuk user tsb, top 3).
- [ ] Sistem cashback/poin sederhana — perlu tabel baru (`loyalty_points`) dan aturan bisnis (mis. 1% dari setiap transaksi sukses); **perlu keputusan bisnis dulu sebelum implementasi**.

### Fase D — PWA Khusus Buyer
- [ ] Prompt kustom "Tambahkan ke Layar Utama" (event `beforeinstallprompt`) di Dashboard, bukan mengandalkan prompt native browser.
- [ ] Cache halaman Katalog untuk mode offline ringan (browsing produk tanpa koneksi) — butuh `vite-plugin-pwa`/service worker, saat ini belum ada (lihat catatan di [FRONTEND_REBUILD_PLAN.md](FRONTEND_REBUILD_PLAN.md)).

### Fase E — Personalisasi
- [ ] "Baru dilihat" (recently viewed products) — simpan di `localStorage` client-side, tidak perlu tabel baru.
- [ ] Rekomendasi produk berdasarkan kategori favorit user (query sederhana: kategori dari `favorite_products` terbanyak → tampilkan produk lain di kategori itu).

## 3. Prioritas yang Disarankan
Mulai dari **Fase A** (murah, dampak langsung ke konsistensi UX), lalu **Fase B** item pertama & kedua (autofill + beli lagi — tidak butuh perubahan skema). Fase B multi-item/keranjang, Fase C poin/cashback, dan Fase D offline caching sebaiknya dikonfirmasi dulu ke stakeholder karena berdampak ke skema database atau butuh keputusan bisnis, bukan sekadar tampilan.

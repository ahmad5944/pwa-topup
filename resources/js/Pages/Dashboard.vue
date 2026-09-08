<script setup>
import { computed, onMounted, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import BalanceBadge from '@/Components/Wallet/BalanceBadge.vue';
import StatusBadge from '@/Components/UI/StatusBadge.vue';
import EmptyState from '@/Components/UI/EmptyState.vue';
import InstallPromptBanner from '@/Components/PWA/InstallPromptBanner.vue';
import { getRecentlyViewedProducts } from '@/Composables/useRecentlyViewedProducts';
import { formatCurrency } from '@/Composables/useCurrency';

defineProps({
    recentTransactions: { type: Array, default: () => [] },
    favoriteProducts: { type: Array, default: () => [] },
    loyaltyPointsBalance: { type: Number, default: 0 },
    promoBanners: { type: Array, default: () => [] },
    frequentProducts: { type: Array, default: () => [] },
    recommendedProducts: { type: Array, default: () => [] },
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const isReseller = computed(() => (page.props.userRoles ?? []).includes('reseller'));
const recentlyViewedProducts = ref([]);

const quickActions = [
    { label: 'Beli Produk', route: 'catalog.index', color: 'bg-primary-600', icon: 'M12 4.5v15m7.5-7.5h-15' },
    { label: 'Deposit', route: 'deposits.index', color: 'bg-emerald-600', icon: 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3M3.75 6h16.5a1.5 1.5 0 011.5 1.5v9a1.5 1.5 0 01-1.5 1.5H3.75a1.5 1.5 0 01-1.5-1.5v-9a1.5 1.5 0 011.5-1.5z' },
    { label: 'Riwayat', route: 'transactions.index', color: 'bg-indigo-600', icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' },
    { label: 'Favorit', route: 'favorites.index', color: 'bg-amber-600', icon: 'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z' },
    { label: 'Poin', route: 'loyalty-points.index', color: 'bg-orange-600', icon: 'M12 3l2.09 4.24L18.5 8l-3.25 3.17.77 4.5L12 13.97 8.98 15.67l.77-4.5L6.5 8l4.41-.76L12 3z' },
];

onMounted(() => {
    recentlyViewedProducts.value = getRecentlyViewedProducts();
});
</script>

<template>
    <CustomerLayout title="Dashboard">
        <div class="space-y-6">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Halo,</p>
                <p class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ user?.name }}</p>
            </div>

            <InstallPromptBanner />

            <BalanceBadge :balance="user?.balance" />

            <div class="rounded-3xl bg-gradient-to-br from-amber-500 to-orange-500 p-5 text-white shadow-lg">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] opacity-80">Cashback</p>
                <div class="mt-2 flex items-end justify-between gap-4">
                    <div>
                        <p class="text-3xl font-bold">{{ loyaltyPointsBalance.toLocaleString('id-ID') }}</p>
                        <p class="text-sm opacity-90">Poin aktif siap dipakai untuk promo berikutnya.</p>
                    </div>
                    <Link :href="route('loyalty-points.index')" class="rounded-full bg-white/15 px-4 py-2 text-sm font-semibold">
                        Lihat riwayat
                    </Link>
                </div>
            </div>

            <section v-if="promoBanners.length" class="grid gap-3">
                <Link
                    v-for="banner in promoBanners"
                    :key="banner.title"
                    :href="banner.href"
                    class="rounded-3xl p-5 text-white shadow-lg transition hover:scale-[1.01]"
                    :class="['bg-gradient-to-br', banner.tone]"
                >
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] opacity-80">Promo</p>
                    <h2 class="mt-2 text-xl font-bold">{{ banner.title }}</h2>
                    <p class="mt-2 text-sm leading-6 opacity-90 max-w-md">{{ banner.description }}</p>
                    <span class="mt-4 inline-flex rounded-full bg-white/15 px-4 py-2 text-sm font-semibold">{{ banner.cta }}</span>
                </Link>
            </section>

            <Link
                v-if="isReseller"
                :href="route('reseller.dashboard')"
                class="flex items-center justify-between rounded-2xl bg-indigo-600 text-white px-5 py-4"
            >
                <span class="font-medium">Area Reseller</span>
                <span>&rarr;</span>
            </Link>

            <div class="grid grid-cols-4 gap-3">
                <Link v-for="action in quickActions" :key="action.route" :href="route(action.route)" class="flex flex-col items-center gap-2 text-center">
                    <span class="h-12 w-12 rounded-2xl flex items-center justify-center text-white" :class="action.color">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="action.icon" />
                        </svg>
                    </span>
                    <span class="text-xs font-medium text-gray-600 dark:text-gray-300">{{ action.label }}</span>
                </Link>
            </div>

            <section>
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-semibold text-gray-900 dark:text-gray-100">Transaksi Terakhir</h2>
                    <Link :href="route('transactions.index')" class="text-sm text-primary-600 dark:text-primary-400">Lihat semua</Link>
                </div>
                <div v-if="recentTransactions.length" class="space-y-2">
                    <Link
                        v-for="transaction in recentTransactions"
                        :key="transaction.id"
                        :href="route('transactions.show', transaction.id)"
                        class="flex items-center justify-between rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4"
                    >
                        <div>
                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ transaction.product?.name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ transaction.target_number }}</p>
                        </div>
                        <div class="text-right space-y-1">
                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ formatCurrency(transaction.price) }}</p>
                            <StatusBadge :status="transaction.status" />
                        </div>
                    </Link>
                </div>
                <EmptyState v-else title="Belum ada transaksi" description="Yuk mulai belanja produk topup pertamamu." />
            </section>

            <section>
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-semibold text-gray-900 dark:text-gray-100">Produk Sering Dibeli</h2>
                    <span class="text-sm text-gray-500 dark:text-gray-400">Beli ulang lebih cepat</span>
                </div>
                <div v-if="frequentProducts.length" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <Link
                        v-for="item in frequentProducts"
                        :key="item.product_id"
                        :href="route('catalog.show', item.product?.id)"
                        class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 flex items-center justify-between gap-3"
                    >
                        <div class="min-w-0">
                            <p class="font-medium text-gray-900 dark:text-gray-100 truncate">{{ item.product?.name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ item.product?.category?.name }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ item.purchase_count }}x dibeli</p>
                            <p class="text-sm font-semibold text-primary-600 dark:text-primary-400">Beli Lagi</p>
                        </div>
                    </Link>
                </div>
                <EmptyState v-else title="Belum ada produk favorit pembelian" description="Setelah transaksi sukses, produk yang sering dibeli akan muncul di sini." />
            </section>

            <section>
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-semibold text-gray-900 dark:text-gray-100">Baru Dilihat</h2>
                    <span class="text-sm text-gray-500 dark:text-gray-400">Disimpan di perangkat ini</span>
                </div>
                <div v-if="recentlyViewedProducts.length" class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <Link
                        v-for="product in recentlyViewedProducts"
                        :key="product.id"
                        :href="route('catalog.show', product.id)"
                        class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-3"
                    >
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 line-clamp-2">{{ product.name }}</p>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ product.category_name }}</p>
                    </Link>
                </div>
                <EmptyState v-else title="Belum ada riwayat lihat" description="Produk yang kamu buka akan muncul di sini." />
            </section>

            <section>
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-semibold text-gray-900 dark:text-gray-100">Rekomendasi Untukmu</h2>
                    <span class="text-sm text-gray-500 dark:text-gray-400">Berdasarkan kategori favorit</span>
                </div>
                <div v-if="recommendedProducts.length" class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <Link
                        v-for="product in recommendedProducts"
                        :key="product.id"
                        :href="route('catalog.show', product.id)"
                        class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-3"
                    >
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 line-clamp-2">{{ product.name }}</p>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ product.category?.name }}</p>
                    </Link>
                </div>
                <EmptyState v-else title="Belum ada rekomendasi" description="Tambahkan favorit dulu agar rekomendasi lebih akurat." />
            </section>

            <section v-if="favoriteProducts.length">
                <h2 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">Favorit</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <Link
                        v-for="favorite in favoriteProducts"
                        :key="favorite.id"
                        :href="route('catalog.show', favorite.product?.id)"
                        class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-3"
                    >
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 line-clamp-2">{{ favorite.product?.name }}</p>
                    </Link>
                </div>
            </section>
        </div>
    </CustomerLayout>
</template>


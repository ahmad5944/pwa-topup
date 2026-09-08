<script setup>
import { Link } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';

const props = defineProps({
    points: { type: Object, required: true },
    balance: { type: Number, default: 0 },
});
</script>

<template>
    <CustomerLayout title="Poin Cashback">
        <div class="max-w-2xl mx-auto space-y-6">
            <div class="rounded-3xl bg-gradient-to-br from-amber-500 to-orange-500 p-5 text-white shadow-lg">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] opacity-80">Total Poin</p>
                <p class="mt-2 text-4xl font-bold">{{ balance.toLocaleString('id-ID') }}</p>
                <p class="mt-2 text-sm opacity-90">Poin cashback dari transaksi sukses yang sudah tercatat.</p>
            </div>

            <section class="space-y-3">
                <div class="flex items-center justify-between">
                    <h1 class="text-lg font-bold text-gray-900 dark:text-gray-100">Riwayat Poin</h1>
                    <Link :href="route('dashboard')" class="text-sm text-primary-600 dark:text-primary-400">Kembali</Link>
                </div>

                <div v-if="points.data.length" class="space-y-2">
                    <div
                        v-for="point in points.data"
                        :key="point.id"
                        class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 flex items-start justify-between gap-4"
                    >
                        <div class="min-w-0">
                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ point.description ?? 'Cashback transaksi' }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ point.transaction?.invoice_no }} &middot; {{ point.transaction?.product?.name }}</p>
                        </div>
                        <p class="shrink-0 text-sm font-semibold text-amber-600 dark:text-amber-400">+{{ point.points }}</p>
                    </div>
                </div>

                <div v-else class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 text-center text-gray-500 dark:text-gray-400">
                    Belum ada poin cashback.
                </div>

                <div v-if="points.links?.length > 3" class="flex flex-wrap gap-2 justify-center pt-2">
                    <Link
                        v-for="(link, index) in points.links"
                        :key="index"
                        :href="link.url ?? '#'"
                        v-html="link.label"
                        class="px-3 py-1.5 rounded-lg text-sm"
                        :class="[link.active ? 'bg-primary-600 text-white' : 'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-800', !link.url && 'opacity-40 pointer-events-none']"
                    />
                </div>
            </section>
        </div>
    </CustomerLayout>
</template>

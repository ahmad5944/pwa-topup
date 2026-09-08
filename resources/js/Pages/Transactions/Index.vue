<script setup>
import { Link, router } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import StatusBadge from '@/Components/UI/StatusBadge.vue';
import EmptyState from '@/Components/UI/EmptyState.vue';
import { formatCurrency } from '@/Composables/useCurrency';

const props = defineProps({
    transactions: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const statuses = ['', 'pending', 'processing', 'success', 'failed', 'refund'];

function filterByStatus(status) {
    router.get(route('transactions.index'), { status }, { preserveState: true, replace: true });
}

function reorderUrl(transaction) {
    return `${route('catalog.show', transaction.product.id)}?target_number=${encodeURIComponent(transaction.target_number)}`;
}
</script>

<template>
    <CustomerLayout title="Transaksi">
        <template #header>
            <div class="flex gap-2 overflow-x-auto">
                <button
                    v-for="status in statuses"
                    :key="status"
                    type="button"
                    class="shrink-0 rounded-full px-4 py-1.5 text-sm font-medium transition"
                    :class="(filters.status ?? '') === status ? 'bg-white text-primary-700' : 'bg-white/10 text-white'"
                    @click="filterByStatus(status)"
                >
                    {{ status === '' ? 'Semua' : status }}
                </button>
            </div>
        </template>

        <div class="space-y-2">
            <div
                v-for="transaction in transactions.data"
                :key="transaction.id"
                class="flex items-center justify-between gap-3 rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4"
            >
                <div class="min-w-0 flex-1">
                    <Link :href="route('transactions.show', transaction.id)" class="block">
                        <p class="font-medium text-gray-900 dark:text-gray-100 truncate">{{ transaction.product?.name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ transaction.invoice_no }} &middot; {{ transaction.target_number }}</p>
                    </Link>
                </div>
                <div class="text-right space-y-2 shrink-0">
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-gray-100">{{ formatCurrency(transaction.price) }}</p>
                        <StatusBadge :status="transaction.status" />
                    </div>
                    <Link
                        :href="reorderUrl(transaction)"
                        class="inline-flex items-center justify-center rounded-full border border-primary-200 bg-primary-50 px-3 py-1 text-xs font-semibold text-primary-700 hover:bg-primary-100 dark:border-primary-900 dark:bg-primary-950/50 dark:text-primary-300"
                    >
                        Beli Lagi
                    </Link>
                </div>
            </div>

            <EmptyState v-if="!transactions.data.length" title="Belum ada transaksi" />

            <div v-if="transactions.links?.length > 3" class="flex flex-wrap gap-2 justify-center pt-4">
                <Link
                    v-for="(link, index) in transactions.links"
                    :key="index"
                    :href="link.url ?? '#'"
                    v-html="link.label"
                    class="px-3 py-1.5 rounded-lg text-sm"
                    :class="[link.active ? 'bg-primary-600 text-white' : 'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-800', !link.url && 'opacity-40 pointer-events-none']"
                />
            </div>
        </div>
    </CustomerLayout>
</template>

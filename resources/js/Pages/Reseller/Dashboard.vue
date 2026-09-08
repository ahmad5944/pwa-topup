<script setup>
import { Link } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import StatusBadge from '@/Components/UI/StatusBadge.vue';
import EmptyState from '@/Components/UI/EmptyState.vue';
import { formatCurrency } from '@/Composables/useCurrency';

defineProps({
    stats: { type: Object, required: true },
    recentCommissions: { type: Array, default: () => [] },
});

const tabs = [
    { label: 'Ringkasan', route: 'reseller.dashboard' },
    { label: 'Downline', route: 'reseller.downlines' },
    { label: 'Komisi', route: 'reseller.commissions' },
];
</script>

<template>
    <CustomerLayout title="Reseller">
        <div class="space-y-6">
            <div class="flex gap-2">
                <Link
                    v-for="tab in tabs"
                    :key="tab.route"
                    :href="route(tab.route)"
                    class="rounded-full px-4 py-1.5 text-sm font-medium"
                    :class="route().current(tab.route) ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300'"
                >
                    {{ tab.label }}
                </Link>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 text-center">
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ stats.downlineCount }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Downline</p>
                </div>
                <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 text-center">
                    <p class="text-lg font-bold text-amber-600 dark:text-amber-400">{{ formatCurrency(stats.pendingCommission) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Komisi Pending</p>
                </div>
                <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 text-center">
                    <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400">{{ formatCurrency(stats.paidCommission) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Komisi Dibayar</p>
                </div>
            </div>

            <section>
                <h2 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">Komisi Terbaru</h2>
                <div v-if="recentCommissions.length" class="space-y-2">
                    <div
                        v-for="commission in recentCommissions"
                        :key="commission.id"
                        class="flex items-center justify-between rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4"
                    >
                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ commission.transaction?.product?.name }}</p>
                        <div class="text-right space-y-1">
                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ formatCurrency(commission.amount) }}</p>
                            <StatusBadge :status="commission.status" />
                        </div>
                    </div>
                </div>
                <EmptyState v-else title="Belum ada komisi" />
            </section>
        </div>
    </CustomerLayout>
</template>

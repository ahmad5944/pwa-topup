<script setup>
import { Link } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import StatusBadge from '@/Components/UI/StatusBadge.vue';
import EmptyState from '@/Components/UI/EmptyState.vue';
import { formatCurrency } from '@/Composables/useCurrency';

defineProps({
    commissions: { type: Object, required: true },
});

const tabs = [
    { label: 'Ringkasan', route: 'reseller.dashboard' },
    { label: 'Downline', route: 'reseller.downlines' },
    { label: 'Komisi', route: 'reseller.commissions' },
];
</script>

<template>
    <CustomerLayout title="Komisi">
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

            <div class="space-y-2">
                <div
                    v-for="commission in commissions.data"
                    :key="commission.id"
                    class="flex items-center justify-between rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4"
                >
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ commission.transaction?.product?.name }}</p>
                    <div class="text-right space-y-1">
                        <p class="font-semibold text-gray-900 dark:text-gray-100">{{ formatCurrency(commission.amount) }}</p>
                        <StatusBadge :status="commission.status" />
                    </div>
                </div>

                <EmptyState v-if="!commissions.data.length" title="Belum ada komisi" />

                <div v-if="commissions.links?.length > 3" class="flex flex-wrap gap-2 justify-center pt-4">
                    <Link
                        v-for="(link, index) in commissions.links"
                        :key="index"
                        :href="link.url ?? '#'"
                        v-html="link.label"
                        class="px-3 py-1.5 rounded-lg text-sm"
                        :class="[link.active ? 'bg-primary-600 text-white' : 'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-800', !link.url && 'opacity-40 pointer-events-none']"
                    />
                </div>
            </div>
        </div>
    </CustomerLayout>
</template>

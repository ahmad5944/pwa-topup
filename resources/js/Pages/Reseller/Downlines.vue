<script setup>
import { Link } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import EmptyState from '@/Components/UI/EmptyState.vue';

defineProps({
    downlines: { type: Array, required: true },
});

const tabs = [
    { label: 'Ringkasan', route: 'reseller.dashboard' },
    { label: 'Downline', route: 'reseller.downlines' },
    { label: 'Komisi', route: 'reseller.commissions' },
];
</script>

<template>
    <CustomerLayout title="Downline">
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
                    v-for="downline in downlines"
                    :key="downline.id"
                    class="flex items-center justify-between rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4"
                >
                    <div>
                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ downline.downline?.name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ downline.downline?.email }}</p>
                    </div>
                </div>

                <EmptyState v-if="!downlines.length" title="Belum ada downline" />
            </div>
        </div>
    </CustomerLayout>
</template>

<script setup>
import { computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StatusBadge from '@/Components/UI/StatusBadge.vue';
import { formatCurrency } from '@/Composables/useCurrency';

const props = defineProps({
    stats: { type: Object, required: true },
    recentTransactions: { type: Array, default: () => [] },
});

const cards = computed(() => ([
    { label: 'Total Users', value: props.stats.totalUsers },
    { label: 'Omzet Sukses', value: formatCurrency(props.stats.totalOmzet) },
    { label: 'Transaksi Sukses', value: props.stats.successCount },
    { label: 'Transaksi Gagal', value: props.stats.failedCount },
    { label: 'Deposit Pending', value: props.stats.pendingDeposits },
]));
</script>

<template>
    <AdminLayout title="Dashboard">
        <template #header>Dashboard</template>

        <div class="space-y-6">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                <div v-for="card in cards" :key="card.label" class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4">
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ card.label }}</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ card.value }}</p>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800 text-left text-gray-500 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-3">Invoice</th>
                            <th class="px-4 py-3">User</th>
                            <th class="px-4 py-3">Produk</th>
                            <th class="px-4 py-3">Harga</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-for="transaction in recentTransactions" :key="transaction.id">
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ transaction.invoice_no }}</td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ transaction.user?.name ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ transaction.product?.name }}</td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ formatCurrency(transaction.price) }}</td>
                            <td class="px-4 py-3"><StatusBadge :status="transaction.status" /></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>

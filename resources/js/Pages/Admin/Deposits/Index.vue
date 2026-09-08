<script setup>
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StatusBadge from '@/Components/UI/StatusBadge.vue';
import EmptyState from '@/Components/UI/EmptyState.vue';
import { formatCurrency } from '@/Composables/useCurrency';

defineProps({
    deposits: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const statuses = ['', 'pending', 'success', 'failed'];

function filterByStatus(status) {
    router.get(route('admin.deposits.index'), { status }, { preserveState: true, replace: true });
}

function approve(deposit) {
    router.post(route('admin.deposits.approve', deposit.id), {}, { preserveScroll: true });
}

function reject(deposit) {
    router.post(route('admin.deposits.reject', deposit.id), {}, { preserveScroll: true });
}
</script>

<template>
    <AdminLayout title="Deposit">
        <template #header>Deposit</template>

        <div class="space-y-4">
            <div class="flex gap-2">
                <button
                    v-for="status in statuses"
                    :key="status"
                    type="button"
                    class="rounded-full px-4 py-1.5 text-sm font-medium"
                    :class="(filters.status ?? '') === status ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300'"
                    @click="filterByStatus(status)"
                >
                    {{ status === '' ? 'Semua' : status }}
                </button>
            </div>

            <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800 text-left text-gray-500 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-3">Order ID</th>
                            <th class="px-4 py-3">User</th>
                            <th class="px-4 py-3">Jumlah</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-for="deposit in deposits.data" :key="deposit.id">
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ deposit.order_id }}</td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ deposit.user?.name }}</td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ formatCurrency(deposit.amount) }}</td>
                            <td class="px-4 py-3"><StatusBadge :status="deposit.status" /></td>
                            <td class="px-4 py-3">
                                <div v-if="deposit.status === 'pending'" class="flex gap-2">
                                    <button type="button" class="text-emerald-600 dark:text-emerald-400 font-medium" @click="approve(deposit)">Setujui</button>
                                    <button type="button" class="text-red-500 font-medium" @click="reject(deposit)">Tolak</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <EmptyState v-if="!deposits.data.length" title="Belum ada deposit" />
            </div>
        </div>
    </AdminLayout>
</template>

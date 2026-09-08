<script setup>
import { Link } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import StatusBadge from '@/Components/UI/StatusBadge.vue';
import EmptyState from '@/Components/UI/EmptyState.vue';
import { formatCurrency } from '@/Composables/useCurrency';

defineProps({
    deposits: { type: Object, required: true },
});
</script>

<template>
    <CustomerLayout title="Deposit">
        <div class="space-y-4">
            <Link :href="route('deposits.create')" class="block text-center rounded-xl bg-primary-600 py-2.5 font-semibold text-white hover:bg-primary-700 transition">
                + Isi Saldo
            </Link>

            <div class="space-y-2">
                <div
                    v-for="deposit in deposits.data"
                    :key="deposit.id"
                    class="flex items-center justify-between rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4"
                >
                    <div>
                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ deposit.order_id }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 capitalize">{{ deposit.method?.replace('_', ' ') }}</p>
                    </div>
                    <div class="text-right space-y-1">
                        <p class="font-semibold text-gray-900 dark:text-gray-100">{{ formatCurrency(deposit.amount) }}</p>
                        <StatusBadge :status="deposit.status" />
                    </div>
                </div>

                <EmptyState v-if="!deposits.data.length" title="Belum ada riwayat deposit" description="Isi saldo untuk mulai belanja produk." />
            </div>
        </div>
    </CustomerLayout>
</template>

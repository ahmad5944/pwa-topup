<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import StatusBadge from '@/Components/UI/StatusBadge.vue';
import { formatCurrency } from '@/Composables/useCurrency';
import { usePolling } from '@/Composables/usePolling';

const props = defineProps({
    transaction: { type: Object, required: true },
});

const isFinal = computed(() => ['success', 'failed', 'refund'].includes(props.transaction.status));

// Keep polling while the order is still being processed by the provider.
if (!isFinal.value) {
    usePolling(4000, ['transaction']);
}
</script>

<template>
    <CustomerLayout :title="`Transaksi ${transaction.invoice_no}`">
        <div class="max-w-lg mx-auto space-y-4">
            <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 text-center">
                <StatusBadge :status="transaction.status" />
                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-3">{{ formatCurrency(transaction.price) }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ transaction.product?.name }}</p>
                <p v-if="!isFinal" class="text-xs text-primary-600 dark:text-primary-400 mt-2">Memeriksa status otomatis...</p>
            </div>

            <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 space-y-3">
                <h2 class="font-semibold text-gray-900 dark:text-gray-100">Detail</h2>
                <dl class="text-sm divide-y divide-gray-100 dark:divide-gray-800">
                    <div class="flex justify-between py-2">
                        <dt class="text-gray-500 dark:text-gray-400">No. Invoice</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ transaction.invoice_no }}</dd>
                    </div>
                    <div class="flex justify-between py-2">
                        <dt class="text-gray-500 dark:text-gray-400">Nomor Tujuan</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ transaction.target_number }}</dd>
                    </div>
                    <div class="flex justify-between py-2">
                        <dt class="text-gray-500 dark:text-gray-400">Ref Provider</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ transaction.provider_ref_id ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between py-2">
                        <dt class="text-gray-500 dark:text-gray-400">Waktu</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ new Date(transaction.created_at).toLocaleString('id-ID') }}</dd>
                    </div>
                </dl>
            </div>

            <Link
                :href="`${route('catalog.show', transaction.product.id)}?target_number=${encodeURIComponent(transaction.target_number)}`"
                class="block text-center rounded-2xl bg-primary-600 px-5 py-3 font-semibold text-white hover:bg-primary-700 transition"
            >
                Beli Lagi
            </Link>

            <div v-if="transaction.logs?.length" class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5">
                <h2 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">Riwayat</h2>
                <ul class="space-y-2 text-sm">
                    <li v-for="log in transaction.logs" :key="log.id" class="flex justify-between text-gray-600 dark:text-gray-300">
                        <span>{{ log.event }}</span>
                        <span class="text-gray-400 dark:text-gray-500">{{ new Date(log.created_at).toLocaleTimeString('id-ID') }}</span>
                    </li>
                </ul>
            </div>

            <Link :href="route('transactions.index')" class="block text-center text-sm text-primary-600 dark:text-primary-400">
                &larr; Kembali ke riwayat
            </Link>
        </div>
    </CustomerLayout>
</template>

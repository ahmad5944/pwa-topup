<script setup>
import { ref } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { formatCurrency } from '@/Composables/useCurrency';

const page = usePage();
const amount = ref(50000);
const processing = ref(false);
const errorMessage = ref('');

const presets = [25000, 50000, 100000, 250000, 500000];

function loadSnapScript() {
    return new Promise((resolve, reject) => {
        if (window.snap) {
            resolve();
            return;
        }

        const script = document.createElement('script');
        script.src = page.props.midtrans.isProduction
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
        script.setAttribute('data-client-key', page.props.midtrans.clientKey ?? '');
        script.onload = resolve;
        script.onerror = reject;
        document.head.appendChild(script);
    });
}

async function submit() {
    errorMessage.value = '';
    processing.value = true;

    try {
        const { data } = await window.axios.post(route('deposits.store'), { amount: amount.value });

        if (data.snap_token) {
            await loadSnapScript();
            window.snap.pay(data.snap_token, {
                onSuccess: () => router.visit(route('deposits.index')),
                onPending: () => router.visit(route('deposits.index')),
                onError: () => { errorMessage.value = 'Pembayaran gagal, silakan coba lagi.'; },
                onClose: () => {},
            });
        } else {
            router.visit(route('deposits.index'));
        }
    } catch (error) {
        errorMessage.value = error.response?.data?.message ?? 'Gagal membuat deposit.';
    } finally {
        processing.value = false;
    }
}
</script>

<template>
    <CustomerLayout title="Isi Saldo">
        <div class="max-w-lg mx-auto space-y-6">
            <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 space-y-4">
                <h1 class="font-semibold text-gray-900 dark:text-gray-100">Isi Saldo</h1>

                <div class="grid grid-cols-3 gap-2">
                    <button
                        v-for="preset in presets"
                        :key="preset"
                        type="button"
                        class="rounded-xl py-2 text-sm font-medium border transition"
                        :class="amount === preset ? 'bg-primary-600 text-white border-primary-600' : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300'"
                        @click="amount = preset"
                    >
                        {{ formatCurrency(preset) }}
                    </button>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jumlah Lain</label>
                    <input
                        v-model.number="amount"
                        type="number"
                        min="10000"
                        step="1000"
                        class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500"
                    >
                </div>

                <p v-if="errorMessage" class="text-sm text-red-600 dark:text-red-400">{{ errorMessage }}</p>

                <button
                    type="button"
                    :disabled="processing || amount < 10000"
                    class="w-full rounded-xl bg-primary-600 py-2.5 font-semibold text-white hover:bg-primary-700 disabled:opacity-50 transition"
                    @click="submit"
                >
                    {{ processing ? 'Memproses...' : 'Bayar Sekarang' }}
                </button>
            </div>
        </div>
    </CustomerLayout>
</template>

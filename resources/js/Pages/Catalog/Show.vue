<script setup>
import { onMounted } from 'vue';
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { formatCurrency } from '@/Composables/useCurrency';
import { recordRecentlyViewedProduct } from '@/Composables/useRecentlyViewedProducts';

const props = defineProps({
    product: { type: Object, required: true },
    favoriteTargetNumbers: { type: Array, default: () => [] },
    suggestedTargetNumber: { type: String, default: '' },
});

const form = useForm({
    product_id: props.product.id,
    target_number: props.suggestedTargetNumber,
});

const hasSavedTargets = computed(() => props.favoriteTargetNumbers.length > 0);

function submit() {
    form.post(route('orders.store'));
}

function useSavedTargetNumber(targetNumber) {
    form.target_number = targetNumber;
}

onMounted(() => {
    recordRecentlyViewedProduct(props.product);
});
</script>

<template>
    <CustomerLayout :title="product.name">
        <div class="space-y-6 max-w-lg mx-auto">
            <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5">
                <p class="text-xs font-medium text-primary-600 dark:text-primary-400">{{ product.brand }} &middot; {{ product.category?.name }}</p>
                <h1 class="text-lg font-bold text-gray-900 dark:text-gray-100 mt-1">{{ product.name }}</h1>
                <p class="text-2xl font-bold text-primary-600 dark:text-primary-400 mt-3">
                    {{ formatCurrency(product.display_price ?? product.price_jual) }}
                </p>
            </div>

            <form class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 space-y-4" @submit.prevent="submit">
                <div v-if="hasSavedTargets" class="space-y-2">
                    <label for="saved_target_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nomor Tersimpan</label>
                    <select
                        id="saved_target_number"
                        class="block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500"
                        @change="useSavedTargetNumber($event.target.value)"
                    >
                        <option :selected="!form.target_number" value="">Pilih nomor tersimpan</option>
                        <option
                            v-for="targetNumber in favoriteTargetNumbers"
                            :key="targetNumber"
                            :value="targetNumber"
                            :selected="form.target_number === targetNumber"
                        >
                            {{ targetNumber }}
                        </option>
                    </select>
                </div>

                <div>
                    <label for="target_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nomor Tujuan</label>
                    <input
                        id="target_number"
                        v-model="form.target_number"
                        type="text"
                        placeholder="Contoh: 081234567890"
                        required
                        class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500"
                    >
                    <p v-if="form.errors.target_number" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.target_number }}</p>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full rounded-xl bg-primary-600 py-2.5 font-semibold text-white hover:bg-primary-700 disabled:opacity-50 transition"
                >
                    Beli Sekarang
                </button>
            </form>
        </div>
    </CustomerLayout>
</template>

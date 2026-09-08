<script setup>
import { Link } from '@inertiajs/vue3';
import { formatCurrency } from '@/Composables/useCurrency';

defineProps({
    product: { type: Object, required: true },
});
</script>

<template>
    <Link :href="route('catalog.show', product.id)" class="group flex flex-col rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 hover:border-primary-400 hover:shadow-md transition">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-primary-600 dark:text-primary-400">{{ product.brand }}</span>
            <span v-if="product.is_active === false" class="text-xs text-red-500">Nonaktif</span>
        </div>
        <p class="mt-2 font-semibold text-gray-900 dark:text-gray-100 line-clamp-2">{{ product.name }}</p>
        <p class="mt-auto pt-3 text-lg font-bold text-primary-600 dark:text-primary-400">
            {{ formatCurrency(product.display_price ?? product.price_jual) }}
        </p>
    </Link>
</template>

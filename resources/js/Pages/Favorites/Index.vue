<script setup>
import { router } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import EmptyState from '@/Components/UI/EmptyState.vue';
import { formatCurrency } from '@/Composables/useCurrency';

defineProps({
    favorites: { type: Array, required: true },
});

function removeFavorite(id) {
    router.delete(route('favorites.destroy', id), { preserveScroll: true });
}
</script>

<template>
    <CustomerLayout title="Favorit">
        <div class="space-y-2">
            <div
                v-for="favorite in favorites"
                :key="favorite.id"
                class="flex items-center justify-between rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4"
            >
                <div>
                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ favorite.product?.name }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ favorite.target_number ?? formatCurrency(favorite.product?.price_jual) }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <a :href="route('catalog.show', favorite.product?.id)" class="text-sm text-primary-600 dark:text-primary-400 font-medium">Beli</a>
                    <button type="button" class="text-sm text-red-500" @click="removeFavorite(favorite.id)">Hapus</button>
                </div>
            </div>

            <EmptyState v-if="!favorites.length" title="Belum ada favorit" description="Tandai produk favoritmu untuk pembelian cepat." />
        </div>
    </CustomerLayout>
</template>

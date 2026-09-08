<script setup>
import { onBeforeUnmount, ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import CategoryTabs from '@/Components/Catalog/CategoryTabs.vue';
import ProductCard from '@/Components/Catalog/ProductCard.vue';
import EmptyState from '@/Components/UI/EmptyState.vue';

const props = defineProps({
    categories: { type: Array, required: true },
    products: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search ?? '');
const loading = ref(false);
let searchTimer = null;

function submitSearch() {
    router.get(route('catalog.index'), { ...props.filters, search: search.value }, {
        preserveState: true,
        replace: true,
        onStart: () => {
            loading.value = true;
        },
        onFinish: () => {
            loading.value = false;
        },
    });
}

watch(search, () => {
    loading.value = true;

    clearTimeout(searchTimer);

    searchTimer = setTimeout(() => {
        submitSearch();
    }, 300);
});

onBeforeUnmount(() => {
    clearTimeout(searchTimer);
});
</script>

<template>
    <CustomerLayout title="Katalog">
        <template #header>
            <input
                v-model="search"
                type="search"
                placeholder="Cari produk..."
                class="w-full rounded-xl border-0 bg-white/10 placeholder-white/70 text-white px-4 py-2.5 focus:ring-2 focus:ring-white/50 focus:bg-white/20"
            />
        </template>

        <div class="space-y-4">
            <CategoryTabs :categories="categories" :active="filters.category ?? ''" />

            <div v-if="loading" class="grid grid-cols-2 sm:grid-cols-3 gap-3 animate-pulse">
                <div v-for="n in 6" :key="n" class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 space-y-3">
                    <div class="h-3 w-16 rounded bg-gray-200 dark:bg-gray-800"></div>
                    <div class="h-10 rounded bg-gray-200 dark:bg-gray-800"></div>
                    <div class="h-4 w-20 rounded bg-gray-200 dark:bg-gray-800"></div>
                </div>
            </div>

            <div v-else-if="products.data.length" class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                <ProductCard v-for="product in products.data" :key="product.id" :product="product" />
            </div>
            <EmptyState v-else title="Produk tidak ditemukan" description="Coba kata kunci atau kategori lain." />

            <div v-if="products.links?.length > 3" class="flex flex-wrap gap-2 justify-center pt-4">
                <Link
                    v-for="(link, index) in products.links"
                    :key="index"
                    :href="link.url ?? '#'"
                    v-html="link.label"
                    class="px-3 py-1.5 rounded-lg text-sm"
                    :class="[link.active ? 'bg-primary-600 text-white' : 'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-800', !link.url && 'opacity-40 pointer-events-none']"
                />
            </div>
        </div>
    </CustomerLayout>
</template>

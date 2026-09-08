<script setup>
import { reactive, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { formatCurrency } from '@/Composables/useCurrency';

const props = defineProps({
    products: { type: Object, required: true },
    categories: { type: Array, required: true },
    filters: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search ?? '');

const drafts = reactive(
    Object.fromEntries(props.products.data.map((p) => [p.id, { price_jual: p.price_jual, is_active: p.is_active }])),
);

function submitSearch() {
    router.get(route('admin.products.index'), { search: search.value }, { preserveState: true, replace: true });
}

function save(productId) {
    router.patch(route('admin.products.update', productId), drafts[productId], { preserveScroll: true });
}
</script>

<template>
    <AdminLayout title="Produk">
        <template #header>Produk</template>

        <div class="space-y-4">
            <input
                v-model="search"
                type="search"
                placeholder="Cari produk..."
                class="w-full max-w-sm rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500"
                @keyup.enter="submitSearch"
            >

            <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800 text-left text-gray-500 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-3">Produk</th>
                            <th class="px-4 py-3">Kategori</th>
                            <th class="px-4 py-3">Provider</th>
                            <th class="px-4 py-3">Harga Jual</th>
                            <th class="px-4 py-3">Aktif</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-for="product in products.data" :key="product.id">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">{{ product.name }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ product.category?.name }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ product.provider?.name }}</td>
                            <td class="px-4 py-3">
                                <input v-model.number="drafts[product.id].price_jual" type="number" class="w-28 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500">
                            </td>
                            <td class="px-4 py-3">
                                <input v-model="drafts[product.id].is_active" type="checkbox" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                            </td>
                            <td class="px-4 py-3">
                                <button type="button" class="text-primary-600 dark:text-primary-400 font-medium" @click="save(product.id)">Simpan</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="products.links?.length > 3" class="flex flex-wrap gap-2 justify-center">
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
    </AdminLayout>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { formatCurrency } from '@/Composables/useCurrency';

const props = defineProps({
    users: { type: Object, required: true },
    priceLevels: { type: Array, required: true },
    filters: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search ?? '');
const creditAmounts = reactive(Object.fromEntries(props.users.data.map((u) => [u.id, 50000])));

function submitSearch() {
    router.get(route('admin.users.index'), { search: search.value }, { preserveState: true, replace: true });
}

function updatePriceLevel(user, priceLevelId) {
    router.patch(route('admin.users.update', user.id), { price_level_id: priceLevelId || null }, { preserveScroll: true });
}

function creditBalance(user) {
    router.post(route('admin.users.credit-balance', user.id), { amount: creditAmounts[user.id] }, { preserveScroll: true });
}
</script>

<template>
    <AdminLayout title="Users">
        <template #header>Users</template>

        <div class="space-y-4">
            <input
                v-model="search"
                type="search"
                placeholder="Cari user..."
                class="w-full max-w-sm rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500"
                @keyup.enter="submitSearch"
            >

            <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800 text-left text-gray-500 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">Saldo</th>
                            <th class="px-4 py-3">Price Level</th>
                            <th class="px-4 py-3">Tambah Saldo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-for="user in users.data" :key="user.id">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900 dark:text-gray-100">{{ user.name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ user.email }}</p>
                            </td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ formatCurrency(user.balance) }}</td>
                            <td class="px-4 py-3">
                                <select
                                    :value="user.price_level_id"
                                    class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500"
                                    @change="updatePriceLevel(user, $event.target.value)"
                                >
                                    <option value="">-</option>
                                    <option v-for="level in priceLevels" :key="level.id" :value="level.id">{{ level.name }}</option>
                                </select>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <input v-model.number="creditAmounts[user.id]" type="number" class="w-28 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500">
                                    <button type="button" class="text-primary-600 dark:text-primary-400 font-medium" @click="creditBalance(user)">Tambah</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="users.links?.length > 3" class="flex flex-wrap gap-2 justify-center">
                <Link
                    v-for="(link, index) in users.links"
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

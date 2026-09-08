<script setup>
import { reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    providers: { type: Array, required: true },
});

const drafts = reactive(
    Object.fromEntries(props.providers.map((p) => [p.id, { priority: p.priority, is_active: p.is_active }])),
);

function save(providerId) {
    router.patch(route('admin.providers.update', providerId), drafts[providerId], { preserveScroll: true });
}
</script>

<template>
    <AdminLayout title="Providers">
        <template #header>Providers</template>

        <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800 text-left text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">Driver</th>
                        <th class="px-4 py-3">Prioritas</th>
                        <th class="px-4 py-3">Aktif</th>
                        <th class="px-4 py-3">Gagal Beruntun</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <tr v-for="provider in providers" :key="provider.id">
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">{{ provider.name }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ provider.driver }}</td>
                        <td class="px-4 py-3">
                            <input v-model.number="drafts[provider.id].priority" type="number" class="w-20 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500">
                        </td>
                        <td class="px-4 py-3">
                            <input v-model="drafts[provider.id].is_active" type="checkbox" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ provider.consecutive_failures }}</td>
                        <td class="px-4 py-3">
                            <button type="button" class="text-primary-600 dark:text-primary-400 font-medium" @click="save(provider.id)">Simpan</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import EmptyState from '@/Components/UI/EmptyState.vue';

defineProps({
    notifications: { type: Object, required: true },
});

function markAsRead(notification) {
    if (notification.is_read) {
        return;
    }

    router.patch(route('notifications.read', notification.id), {}, { preserveScroll: true });
}
</script>

<template>
    <CustomerLayout title="Notifikasi">
        <div class="space-y-2">
            <button
                v-for="notification in notifications.data"
                :key="notification.id"
                type="button"
                class="w-full text-left flex items-start gap-3 rounded-2xl border p-4 transition"
                :class="notification.is_read
                    ? 'border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900'
                    : 'border-primary-200 dark:border-primary-800 bg-primary-50 dark:bg-primary-900/20'"
                @click="markAsRead(notification)"
            >
                <span class="h-2 w-2 mt-2 rounded-full shrink-0" :class="notification.is_read ? 'bg-transparent' : 'bg-primary-500'" />
                <div>
                    <p class="text-sm text-gray-900 dark:text-gray-100">{{ notification.message }}</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ new Date(notification.created_at).toLocaleString('id-ID') }}</p>
                </div>
            </button>

            <EmptyState v-if="!notifications.data.length" title="Belum ada notifikasi" />
        </div>
    </CustomerLayout>
</template>

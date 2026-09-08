<script setup>
defineProps({
    title: { type: String, default: 'Belum ada data' },
    description: { type: String, default: '' },
});
</script>

<template>
    <div class="flex flex-col items-center justify-center py-16 text-center">
        <div class="h-16 w-16 rounded-full bg-primary-50 dark:bg-primary-900/30 flex items-center justify-center mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 13.5h6m-6-3h6m3.75 9H5.25a1.5 1.5 0 01-1.5-1.5V5.25a1.5 1.5 0 011.5-1.5h5.379a1.5 1.5 0 011.06.44l6.371 6.371a1.5 1.5 0 01.44 1.06V18a1.5 1.5 0 01-1.5 1.5z" />
            </svg>
        </div>
        <p class="font-semibold text-gray-900 dark:text-gray-100">{{ title }}</p>
        <p v-if="description" class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-xs">{{ description }}</p>
        <div class="mt-4">
            <slot />
        </div>
    </div>
</template>

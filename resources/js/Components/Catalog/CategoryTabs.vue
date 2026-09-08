<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    categories: { type: Array, required: true },
    active: { type: String, default: '' },
});
</script>

<template>
    <div class="flex gap-2 overflow-x-auto pb-2 -mx-1 px-1">
        <Link
            :href="route('catalog.index')"
            class="shrink-0 rounded-full px-4 py-1.5 text-sm font-medium transition"
            :class="active === '' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300'"
        >
            Semua
        </Link>
        <Link
            v-for="category in categories"
            :key="category.id"
            :href="route('catalog.index', { category: category.slug })"
            class="shrink-0 rounded-full px-4 py-1.5 text-sm font-medium transition"
            :class="active === category.slug ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300'"
        >
            {{ category.name }}
        </Link>
    </div>
</template>

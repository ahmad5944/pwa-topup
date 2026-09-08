<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import DarkModeToggle from '@/Components/UI/DarkModeToggle.vue';
import NotificationBell from '@/Components/Notifications/NotificationBell.vue';
import FlashToast from '@/Components/UI/FlashToast.vue';

defineProps({
    title: { type: String, default: '' },
});

const page = usePage();
const unread = computed(() => page.props.unreadNotificationsCount ?? 0);

const navItems = [
    { label: 'Beranda', route: 'dashboard', icon: 'M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25' },
    { label: 'Katalog', route: 'catalog.index', icon: 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z' },
    { label: 'Transaksi', route: 'transactions.index', icon: 'M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z' },
    { label: 'Deposit', route: 'deposits.index', icon: 'M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 9m18 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v3' },
    { label: 'Akun', route: 'account.show', icon: 'M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z' },
];
</script>

<template>
    <div class="min-h-screen flex flex-col bg-gray-50 dark:bg-gray-950">
        <Head :title="title" />
        <FlashToast />

        <header class="sticky top-0 z-20 bg-gradient-to-r from-primary-600 to-primary-700 text-white shadow-md">
            <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
                <Link :href="route('dashboard')" class="font-bold text-lg tracking-tight">Topup</Link>
                <div class="flex items-center gap-1">
                    <NotificationBell :count="unread" class="!text-white hover:!bg-white/10" />
                    <DarkModeToggle class="!text-white hover:!bg-white/10" />
                </div>
            </div>
            <div v-if="$slots.header" class="max-w-5xl mx-auto px-4 pb-4">
                <slot name="header" />
            </div>
        </header>

        <main class="flex-1 max-w-5xl w-full mx-auto px-4 py-6 pb-24">
            <slot />
        </main>

        <nav class="fixed bottom-0 inset-x-0 z-20 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800" style="padding-bottom: env(safe-area-inset-bottom)">
            <div class="max-w-5xl mx-auto grid grid-cols-5">
                <Link
                    v-for="item in navItems"
                    :key="item.route"
                    :href="route(item.route)"
                    class="flex flex-col items-center justify-center py-2 text-[11px] gap-1"
                    :class="route().current(item.route) ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400 dark:text-gray-500'"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
                    </svg>
                    {{ item.label }}
                </Link>
            </div>
        </nav>
    </div>
</template>

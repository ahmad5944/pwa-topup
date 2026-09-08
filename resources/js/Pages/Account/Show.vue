<script setup>
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();

defineProps({
    loyaltyPointsBalance: { type: Number, default: 0 },
});
</script>

<template>
    <CustomerLayout title="Akun">
        <div class="space-y-6 max-w-2xl mx-auto">
            <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5">
                <p class="text-sm text-gray-500 dark:text-gray-400">Profil</p>
                <h1 class="mt-1 text-2xl font-bold text-gray-900 dark:text-gray-100">{{ page.props.auth.user.name }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ page.props.auth.user.email }}</p>
                <p class="mt-3 inline-flex rounded-full bg-primary-50 px-3 py-1 text-xs font-medium text-primary-700 dark:bg-primary-900/30 dark:text-primary-300">
                    {{ (page.props.userRoles ?? []).join(', ') }}
                </p>
            </div>

            <div class="rounded-2xl bg-gradient-to-br from-amber-500 to-orange-500 p-5 text-white shadow-lg">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] opacity-80">Cashback</p>
                <div class="mt-2 flex items-center justify-between gap-4">
                    <div>
                        <p class="text-3xl font-bold">{{ loyaltyPointsBalance.toLocaleString('id-ID') }}</p>
                        <p class="text-sm opacity-90">Poin cashback aktif yang terkumpul.</p>
                    </div>
                    <Link :href="route('loyalty-points.index')" class="rounded-full bg-white/15 px-4 py-2 text-sm font-semibold">
                        Riwayat poin
                    </Link>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <Link :href="route('profile.show')" class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 hover:border-primary-400 transition">
                    <p class="font-semibold text-gray-900 dark:text-gray-100">Kelola Profil Jetstream</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Ubah nama, email, password, dan keamanan akun.</p>
                </Link>

                <Link :href="route('logout')" method="post" as="button" class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 text-left hover:border-red-400 transition">
                    <p class="font-semibold text-red-600 dark:text-red-400">Keluar</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Logout dari aplikasi ini.</p>
                </Link>
            </div>
        </div>
    </CustomerLayout>
</template>

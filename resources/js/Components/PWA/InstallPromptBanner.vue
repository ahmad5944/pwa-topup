<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

const emit = defineEmits(['installed']);

const installPromptEvent = ref(null);
const dismissed = ref(false);
const visible = ref(false);
const storageKey = 'topup_pwa_install_prompt_dismissed';

const isStandalone = computed(() => {
    if (typeof window === 'undefined') {
        return false;
    }

    return window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
});

const handleBeforeInstallPrompt = (event) => {
    event.preventDefault();
    installPromptEvent.value = event;
    syncVisibility();
};

const handleAppInstalled = () => {
    installPromptEvent.value = null;
    syncVisibility();
};

function syncVisibility() {
    visible.value = Boolean(installPromptEvent.value) && !dismissed.value && !isStandalone.value;
}

function dismiss() {
    dismissed.value = true;
    visible.value = false;
    localStorage.setItem(storageKey, '1');
}

async function installApp() {
    if (!installPromptEvent.value) {
        return;
    }

    installPromptEvent.value.prompt();
    const choice = await installPromptEvent.value.userChoice;

    if (choice?.outcome === 'accepted') {
        emit('installed');
    }

    installPromptEvent.value = null;
    syncVisibility();
}

onMounted(() => {
    dismissed.value = localStorage.getItem(storageKey) === '1';

    window.addEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
    window.addEventListener('appinstalled', handleAppInstalled);

    syncVisibility();
});

onBeforeUnmount(() => {
    window.removeEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
    window.removeEventListener('appinstalled', handleAppInstalled);
});
</script>

<template>
    <transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-2"
    >
        <div
            v-if="visible"
            class="rounded-3xl border border-primary-200 bg-white/95 p-4 shadow-xl backdrop-blur dark:border-primary-900 dark:bg-gray-900/95"
        >
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-primary-600 dark:text-primary-400">PWA</p>
                    <h2 class="text-base font-bold text-gray-900 dark:text-gray-100">Pasang aplikasi di layar utama</h2>
                    <p class="text-sm leading-6 text-gray-600 dark:text-gray-300">
                        Akses topup lebih cepat seperti aplikasi native, langsung dari layar utama ponsel.
                    </p>
                </div>
                <button
                    type="button"
                    class="text-xs font-semibold uppercase tracking-wide text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
                    @click="dismiss"
                >
                    Tutup
                </button>
            </div>

            <div class="mt-4 flex flex-wrap gap-2">
                <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-full bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700 transition"
                    @click="installApp"
                >
                    Pasang Aplikasi
                </button>
                <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-full border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 transition"
                    @click="dismiss"
                >
                    Nanti Saja
                </button>
            </div>
        </div>
    </transition>
</template>

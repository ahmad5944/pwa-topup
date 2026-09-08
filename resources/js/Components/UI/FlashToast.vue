<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const visible = ref(false);
const timer = ref(null);

const flashMessage = computed(() => page.props.flash?.success || page.props.flash?.error || '');
const tone = computed(() => (page.props.flash?.error ? 'error' : 'success'));

function dismiss() {
    visible.value = false;
    if (timer.value) {
        clearTimeout(timer.value);
        timer.value = null;
    }
}

function show() {
    if (!flashMessage.value) {
        return;
    }

    visible.value = true;
    if (timer.value) {
        clearTimeout(timer.value);
    }

    timer.value = setTimeout(() => {
        visible.value = false;
    }, 4000);
}

watch(flashMessage, show, { immediate: true });

onMounted(show);
onBeforeUnmount(dismiss);
</script>

<template>
    <transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0 translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-2"
    >
        <div
            v-if="visible && flashMessage"
            class="fixed top-4 left-4 right-4 z-50 mx-auto max-w-md rounded-2xl border p-4 shadow-lg backdrop-blur"
            :class="tone === 'error'
                ? 'border-red-200 bg-red-50/95 text-red-800 dark:border-red-900 dark:bg-red-950/95 dark:text-red-200'
                : 'border-emerald-200 bg-emerald-50/95 text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/95 dark:text-emerald-200'"
        >
            <div class="flex items-start justify-between gap-4">
                <p class="text-sm font-medium leading-6">{{ flashMessage }}</p>
                <button type="button" class="text-xs font-semibold uppercase tracking-wide opacity-70 hover:opacity-100" @click="dismiss">
                    Tutup
                </button>
            </div>
        </div>
    </transition>
</template>

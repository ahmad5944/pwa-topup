import { onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';

/** Reload only the given Inertia props on an interval, preserving scroll/state. */
export function usePolling(intervalMs = 5000, only = []) {
    let timer = null;

    onMounted(() => {
        timer = setInterval(() => {
            router.reload({ only, preserveScroll: true, preserveState: true });
        }, intervalMs);
    });

    onUnmounted(() => clearInterval(timer));
}

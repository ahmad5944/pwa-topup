import { ref, watchEffect } from 'vue';

const isDark = ref(localStorage.theme === 'dark'
    || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches));

watchEffect(() => {
    document.documentElement.classList.toggle('dark', isDark.value);
    localStorage.theme = isDark.value ? 'dark' : 'light';
});

export function useDarkMode() {
    const toggle = () => {
        isDark.value = !isDark.value;
    };

    return { isDark, toggle };
}

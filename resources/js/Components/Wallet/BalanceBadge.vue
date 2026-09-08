<script setup>
import { formatCurrency } from '@/Composables/useCurrency';

defineProps({
    balance: { type: [Number, String], default: 0 },
});
</script>

<template>
    <div class="rounded-2xl bg-gradient-to-br from-primary-600 to-primary-700 text-white px-5 py-4 shadow-lg shadow-primary-600/20">
        <p class="text-xs uppercase tracking-wide text-primary-100">Saldo</p>
        <p class="text-2xl font-bold mt-1">{{ formatCurrency(balance) }}</p>
    </div>
</template>

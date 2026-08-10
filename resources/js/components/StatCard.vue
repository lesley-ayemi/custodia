<script setup lang="ts">
defineProps<{ label: string; value: string | number }>();
</script>

<template>
    <div class="rounded-lg border border-slate-200 bg-white p-6">
        <p class="text-xs font-medium tracking-wider text-slate-500 uppercase">{{ label }}</p>
        <p class="mt-2 text-3xl font-semibold text-slate-900">{{ value }}</p>
    </div>
</template>

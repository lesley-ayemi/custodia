<script setup lang="ts">
import { ref } from 'vue';

const message = ref<string>('Custodia scaffold is running.');
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-slate-50">
        <div class="rounded-lg border border-slate-200 bg-white px-8 py-6 shadow-sm">
            <h1 class="text-xl font-semibold text-slate-900">Custodia</h1>
            <p class="mt-2 text-sm text-slate-600">{{ message }}</p>
        </div>
    </div>
</template>

<script setup lang="ts">
import type { Component } from 'vue';
import type { RouteLocationRaw } from 'vue-router';

defineProps<{
    label: string;
    value: string | number;
    icon: Component;
    iconClass?: string;
    to?: RouteLocationRaw;
    linkLabel?: string;
}>();
</script>

<template>
    <div class="surface-card">
        <div class="flex items-start justify-between">
            <p class="text-xs font-semibold tracking-wider text-slate-500 uppercase">{{ label }}</p>
            <span class="icon-badge" :class="iconClass ?? 'bg-primary-600'">
                <component :is="icon" :size="19" />
            </span>
        </div>
        <p class="mt-3 text-3xl font-bold text-slate-900">{{ value }}</p>
        <router-link v-if="to" :to="to" class="mt-2 inline-block text-sm font-medium text-primary-600 hover:underline">
            {{ linkLabel }}
        </router-link>
    </div>
</template>

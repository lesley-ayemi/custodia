<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{ status: string }>();

const colorClasses: Record<string, string> = {
    in_custody: 'bg-emerald-100 text-emerald-800',
    released: 'bg-slate-100 text-slate-600',
    transferred: 'bg-amber-100 text-amber-800',
    reported: 'bg-amber-100 text-amber-800',
    under_review: 'bg-blue-100 text-blue-800',
    resolved: 'bg-emerald-100 text-emerald-800',
    open: 'bg-blue-100 text-blue-800',
    adjourned: 'bg-amber-100 text-amber-800',
    closed: 'bg-slate-100 text-slate-600',
    scheduled: 'bg-blue-100 text-blue-800',
    completed: 'bg-emerald-100 text-emerald-800',
    cancelled: 'bg-slate-100 text-slate-600',
    enrolled: 'bg-blue-100 text-blue-800',
    withdrawn: 'bg-slate-100 text-slate-600',
    active: 'bg-emerald-100 text-emerald-800',
    inactive: 'bg-slate-100 text-slate-600',
    in_progress: 'bg-blue-100 text-blue-800',
};

const label = computed(() =>
    props.status
        .split('_')
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' '),
);

const classes = computed(() => colorClasses[props.status] ?? 'bg-slate-100 text-slate-600');
</script>

<template>
    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium" :class="classes">
        {{ label }}
    </span>
</template>

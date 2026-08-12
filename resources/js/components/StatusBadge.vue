<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{ status: string }>();

const colorClasses: Record<string, string> = {
    in_custody: 'bg-emerald-100 text-emerald-800',
    released: 'bg-slate-100 text-slate-600',
    transferred: 'bg-amber-100 text-amber-800',
    reported: 'bg-amber-100 text-amber-800',
    under_review: 'bg-primary-100 text-primary-700',
    resolved: 'bg-emerald-100 text-emerald-800',
    open: 'bg-primary-100 text-primary-700',
    adjourned: 'bg-amber-100 text-amber-800',
    closed: 'bg-slate-100 text-slate-600',
    scheduled: 'bg-primary-100 text-primary-700',
    completed: 'bg-emerald-100 text-emerald-800',
    cancelled: 'bg-slate-100 text-slate-600',
    enrolled: 'bg-primary-100 text-primary-700',
    withdrawn: 'bg-slate-100 text-slate-600',
    active: 'bg-emerald-100 text-emerald-800',
    inactive: 'bg-slate-100 text-slate-600',
    in_progress: 'bg-primary-100 text-primary-700',
    discontinued: 'bg-slate-100 text-slate-600',
    low: 'bg-slate-100 text-slate-600',
    medium: 'bg-amber-100 text-amber-800',
    high: 'bg-red-100 text-red-800',
    pending: 'bg-amber-100 text-amber-800',
    approved: 'bg-emerald-100 text-emerald-800',
    rejected: 'bg-red-100 text-red-800',
    checked_in: 'bg-primary-100 text-primary-700',
    requested: 'bg-amber-100 text-amber-800',
    departed: 'bg-primary-100 text-primary-700',
    arrived: 'bg-primary-100 text-primary-700',
    returned: 'bg-emerald-100 text-emerald-800',
    draft: 'bg-slate-100 text-slate-600',
    processing: 'bg-primary-100 text-primary-700',
    awaiting_medical: 'bg-amber-100 text-amber-800',
    awaiting_housing: 'bg-amber-100 text-amber-800',
    maximum: 'bg-red-100 text-red-800',
    custodial: 'bg-red-100 text-red-800',
    suspended: 'bg-amber-100 text-amber-800',
    life: 'bg-slate-800 text-white',
    convicted: 'bg-slate-100 text-slate-600',
    on_appeal: 'bg-primary-100 text-primary-700',
    discharged: 'bg-emerald-100 text-emerald-800',
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
    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold" :class="classes">
        <span class="h-1.5 w-1.5 rounded-full bg-current opacity-60" />
        {{ label }}
    </span>
</template>

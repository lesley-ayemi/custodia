<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{ occupied: number; capacity: number }>();

const percent = computed(() => (props.capacity === 0 ? 0 : Math.round((props.occupied / props.capacity) * 100)));

const colorClass = computed(() => {
    if (percent.value >= 90) return 'bg-red-500';
    if (percent.value >= 70) return 'bg-amber-500';
    return 'bg-primary-500';
});
</script>

<template>
    <div class="flex items-center gap-3">
        <div class="h-2.5 flex-1 overflow-hidden rounded-full bg-slate-100">
            <div class="h-full rounded-full transition-all" :class="colorClass" :style="{ width: `${percent}%` }" />
        </div>
        <span class="w-10 shrink-0 text-right text-xs font-semibold text-slate-500">{{ percent }}%</span>
    </div>
</template>

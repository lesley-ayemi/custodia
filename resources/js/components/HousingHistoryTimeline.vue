<script setup lang="ts">
import type { HousingAssignment } from '../types/housing';

defineProps<{ history: HousingAssignment[] }>();

function formatDate(value: string): string {
    return new Date(value).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
}
</script>

<template>
    <ol class="space-y-3">
        <li v-for="entry in history" :key="entry.id" class="flex items-start gap-3 text-sm">
            <span class="mt-1 h-2 w-2 shrink-0 rounded-full" :class="entry.ended_at ? 'bg-slate-300' : 'bg-emerald-500'" />
            <div>
                <p class="font-medium text-slate-900">{{ formatDate(entry.started_at) }}</p>
                <p class="text-slate-600">{{ entry.block_name }} / {{ entry.wing_name }} / {{ entry.cell_code }}</p>
                <p v-if="entry.ended_at" class="text-xs text-slate-400">until {{ formatDate(entry.ended_at) }}</p>
                <p v-else class="text-xs text-emerald-600">Current</p>
            </div>
        </li>
        <li v-if="history.length === 0" class="text-sm text-slate-500">No housing history yet.</li>
    </ol>
</template>

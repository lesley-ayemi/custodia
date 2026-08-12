<script setup lang="ts">
import { onMounted } from 'vue';
import DashboardLayout from '../../layouts/DashboardLayout.vue';
import { useAuditStore } from '../../stores/audit';
import type { AuditLog } from '../../types/audit';

const store = useAuditStore();

const actionLabels: Record<string, string> = {
    created: 'Created',
    updated: 'Updated',
    archived: 'Archived',
    resolved: 'Resolved',
    'housing assignment changed': 'Changed housing assignment',
};

function actionLabel(action: string): string {
    return actionLabels[action] ?? action;
}

function formatDateTime(value: string): string {
    return new Date(value).toLocaleString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function changes(log: AuditLog): { field: string; from: string; to: string }[] {
    const fields = new Set([...Object.keys(log.old_values ?? {}), ...Object.keys(log.new_values ?? {})]);

    return Array.from(fields).map((field) => ({
        field,
        from: log.old_values?.[field] ?? '—',
        to: log.new_values?.[field] ?? '—',
    }));
}

function goToPage(page: number): void {
    store.fetchList(page);
}

onMounted(() => store.fetchList());
</script>

<template>
    <DashboardLayout>
        <h1 class="text-2xl font-bold text-slate-900">Audit Log</h1>

        <div class="mt-6 space-y-4">
            <div v-for="log in store.logs" :key="log.id" class="rounded-xl border border-slate-100 bg-white p-4 shadow-sm">
                <div class="flex items-baseline justify-between text-sm">
                    <span class="font-medium text-slate-900">{{ formatDateTime(log.created_at) }}</span>
                    <span class="text-slate-500">{{ log.user_name }}</span>
                </div>
                <p class="mt-2 text-sm font-medium text-slate-700">
                    {{ actionLabel(log.action) }} <span class="text-slate-400">· {{ log.entity_type }} #{{ log.entity_id }}</span>
                </p>
                <ul v-if="changes(log).length > 0" class="mt-2 space-y-1 text-sm text-slate-600">
                    <li v-for="change in changes(log)" :key="change.field">
                        <span class="text-slate-400">{{ change.field }}:</span>
                        {{ change.from }} → {{ change.to }}
                    </li>
                </ul>
            </div>
            <p v-if="!store.loading && store.logs.length === 0" class="text-sm text-slate-500">No audit activity yet.</p>
        </div>

        <div v-if="store.lastPage > 1" class="mt-4 flex items-center justify-between text-sm text-slate-600">
            <span>Page {{ store.currentPage }} of {{ store.lastPage }}</span>
            <div class="flex gap-2">
                <button
                    type="button"
                    :disabled="store.currentPage <= 1"
                    class="btn-secondary-sm"
                    @click="goToPage(store.currentPage - 1)"
                >
                    Previous
                </button>
                <button
                    type="button"
                    :disabled="store.currentPage >= store.lastPage"
                    class="btn-secondary-sm"
                    @click="goToPage(store.currentPage + 1)"
                >
                    Next
                </button>
            </div>
        </div>
    </DashboardLayout>
</template>

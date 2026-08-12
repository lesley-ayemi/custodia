<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import DashboardLayout from '../../layouts/DashboardLayout.vue';
import StatusBadge from '../../components/StatusBadge.vue';
import DataTable from '../../components/DataTable.vue';
import type { DataTableColumn } from '../../components/DataTable.vue';
import { useIncidentStore } from '../../stores/incident';
import { useAuthStore } from '../../stores/auth';
import type { Incident, IncidentStatus } from '../../types/incident';

const store = useIncidentStore();
const auth = useAuthStore();
const statusFilter = ref<IncidentStatus | ''>('');
const actingId = ref<number | null>(null);

const tabs: { label: string; value: IncidentStatus | '' }[] = [
    { label: 'All', value: '' },
    { label: 'Reported', value: 'reported' },
    { label: 'Under Review', value: 'under_review' },
    { label: 'Resolved', value: 'resolved' },
];

const columns = computed<DataTableColumn[]>(() => {
    const base: DataTableColumn[] = [
        { key: 'incident_number', label: 'Incident #', sortable: true },
        { key: 'prisoner_name', label: 'Prisoner', sortable: true },
        { key: 'type', label: 'Type' },
        { key: 'severity', label: 'Severity', sortable: true },
        { key: 'occurred_at', label: 'Occurred', sortable: true },
        { key: 'status', label: 'Status' },
    ];

    if (auth.hasRole('supervisor', 'admin')) base.push({ key: 'actions', label: 'Actions' });
    if (auth.hasRole('admin')) base.push({ key: 'edit', label: '' });

    return base;
});

function load(): void {
    store.fetchList(statusFilter.value);
}

function setFilter(value: IncidentStatus | ''): void {
    statusFilter.value = value;
    load();
}

async function review(id: number): Promise<void> {
    actingId.value = id;
    try {
        await store.markUnderReview(id);
        load();
    } finally {
        actingId.value = null;
    }
}

async function resolve(id: number): Promise<void> {
    actingId.value = id;
    try {
        await store.resolve(id);
        load();
    } finally {
        actingId.value = null;
    }
}

function formatDate(value: string): string {
    return new Date(value).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
}

onMounted(load);
</script>

<template>
    <DashboardLayout>
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-slate-900">Incidents</h1>
            <router-link
                v-if="auth.hasRole('officer', 'admin')"
                :to="{ name: 'incidents.create' }"
                class="btn-primary"
            >
                Report incident
            </router-link>
        </div>

        <div class="mt-4 flex gap-2">
            <button
                v-for="tab in tabs"
                :key="tab.label"
                type="button"
                :class="statusFilter === tab.value ? 'tab-pill-active' : 'tab-pill-inactive'"
                @click="setFilter(tab.value)"
            >
                {{ tab.label }}
            </button>
        </div>

        <div class="mt-4">
            <DataTable
                :columns="columns"
                :rows="store.incidents as unknown as Record<string, unknown>[]"
                :loading="store.loading"
                empty-message="No incidents found."
                searchable
                search-placeholder="Search incidents…"
            >
                <template #cell-incident_number="{ value }">
                    <span class="font-medium text-slate-900">{{ value }}</span>
                </template>
                <template #cell-type="{ value }">
                    <span class="text-slate-500 capitalize">{{ String(value).replaceAll('_', ' ') }}</span>
                </template>
                <template #cell-severity="{ value }">
                    <span class="text-slate-500 capitalize">{{ value }}</span>
                </template>
                <template #cell-occurred_at="{ value }">
                    <span class="text-slate-500">{{ formatDate(value as string) }}</span>
                </template>
                <template #cell-status="{ row }">
                    <StatusBadge :status="(row as unknown as Incident).status" />
                </template>
                <template #cell-actions="{ row }">
                    <button
                        v-if="(row as unknown as Incident).status === 'reported'"
                        type="button"
                        :disabled="actingId === (row as unknown as Incident).id"
                        class="text-primary-600 hover:underline disabled:opacity-50"
                        @click="review((row as unknown as Incident).id)"
                    >
                        Review
                    </button>
                    <button
                        v-if="(row as unknown as Incident).status === 'under_review'"
                        type="button"
                        :disabled="actingId === (row as unknown as Incident).id"
                        class="text-emerald-700 hover:underline disabled:opacity-50"
                        @click="resolve((row as unknown as Incident).id)"
                    >
                        Resolve
                    </button>
                </template>
                <template #cell-edit="{ row }">
                    <router-link
                        :to="{ name: 'incidents.edit', params: { id: (row as unknown as Incident).id } }"
                        class="text-slate-600 hover:underline"
                    >
                        Edit
                    </router-link>
                </template>
            </DataTable>
        </div>
    </DashboardLayout>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import DashboardLayout from '../../layouts/DashboardLayout.vue';
import StatusBadge from '../../components/StatusBadge.vue';
import { useIncidentStore } from '../../stores/incident';
import { useAuthStore } from '../../stores/auth';
import type { IncidentStatus } from '../../types/incident';

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

        <div class="mt-4 surface-shell">
            <table class="w-full text-sm">
                <thead class="border-b border-slate-100 bg-slate-50/60 text-left">
                    <tr>
                        <th class="table-header-cell">Incident #</th>
                        <th class="table-header-cell">Prisoner</th>
                        <th class="table-header-cell">Type</th>
                        <th class="table-header-cell">Severity</th>
                        <th class="table-header-cell">Occurred</th>
                        <th class="table-header-cell">Status</th>
                        <th v-if="auth.hasRole('supervisor', 'admin')" class="table-header-cell">Actions</th>
                        <th v-if="auth.hasRole('admin')" class="table-header-cell"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="incident in store.incidents" :key="incident.id" class="table-row">
                        <td class="px-4 py-3 font-medium text-slate-900">{{ incident.incident_number }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ incident.prisoner_name }}</td>
                        <td class="px-4 py-3 text-slate-500 capitalize">{{ incident.type.replaceAll('_', ' ') }}</td>
                        <td class="px-4 py-3 text-slate-500 capitalize">{{ incident.severity }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ formatDate(incident.occurred_at) }}</td>
                        <td class="px-4 py-3"><StatusBadge :status="incident.status" /></td>
                        <td v-if="auth.hasRole('supervisor', 'admin')" class="space-x-2 px-4 py-3">
                            <button
                                v-if="incident.status === 'reported'"
                                type="button"
                                :disabled="actingId === incident.id"
                                class="text-primary-600 hover:underline disabled:opacity-50"
                                @click="review(incident.id)"
                            >
                                Review
                            </button>
                            <button
                                v-if="incident.status === 'under_review'"
                                type="button"
                                :disabled="actingId === incident.id"
                                class="text-emerald-700 hover:underline disabled:opacity-50"
                                @click="resolve(incident.id)"
                            >
                                Resolve
                            </button>
                        </td>
                        <td v-if="auth.hasRole('admin')" class="px-4 py-3">
                            <router-link :to="{ name: 'incidents.edit', params: { id: incident.id } }" class="text-slate-600 hover:underline">
                                Edit
                            </router-link>
                        </td>
                    </tr>
                    <tr v-if="!store.loading && store.incidents.length === 0">
                        <td colspan="8" class="px-4 py-6 text-center text-slate-500">No incidents found.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </DashboardLayout>
</template>

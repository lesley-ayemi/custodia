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
            <h1 class="text-xl font-semibold text-slate-900">Incidents</h1>
            <router-link
                v-if="auth.hasRole('officer')"
                :to="{ name: 'incidents.create' }"
                class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800"
            >
                Report incident
            </router-link>
        </div>

        <div class="mt-4 flex gap-2">
            <button
                v-for="tab in tabs"
                :key="tab.label"
                type="button"
                class="rounded-full px-3 py-1 text-sm"
                :class="statusFilter === tab.value ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                @click="setFilter(tab.value)"
            >
                {{ tab.label }}
            </button>
        </div>

        <div class="mt-4 overflow-hidden rounded-lg border border-slate-200 bg-white">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-medium tracking-wider text-slate-500 uppercase">
                    <tr>
                        <th class="px-4 py-3">Incident #</th>
                        <th class="px-4 py-3">Prisoner</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Severity</th>
                        <th class="px-4 py-3">Occurred</th>
                        <th class="px-4 py-3">Status</th>
                        <th v-if="auth.hasRole('supervisor')" class="px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="incident in store.incidents" :key="incident.id">
                        <td class="px-4 py-3 font-medium text-slate-900">{{ incident.incident_number }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ incident.prisoner_name }}</td>
                        <td class="px-4 py-3 text-slate-500 capitalize">{{ incident.type.replaceAll('_', ' ') }}</td>
                        <td class="px-4 py-3 text-slate-500 capitalize">{{ incident.severity }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ formatDate(incident.occurred_at) }}</td>
                        <td class="px-4 py-3"><StatusBadge :status="incident.status" /></td>
                        <td v-if="auth.hasRole('supervisor')" class="space-x-2 px-4 py-3">
                            <button
                                v-if="incident.status === 'reported'"
                                type="button"
                                :disabled="actingId === incident.id"
                                class="text-blue-700 hover:underline disabled:opacity-50"
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
                    </tr>
                    <tr v-if="!store.loading && store.incidents.length === 0">
                        <td colspan="7" class="px-4 py-6 text-center text-slate-500">No incidents found.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </DashboardLayout>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import DashboardLayout from '../../layouts/DashboardLayout.vue';
import StatusBadge from '../../components/StatusBadge.vue';
import { useAdmissionStore } from '../../stores/admission';
import { useAuthStore } from '../../stores/auth';
import type { AdmissionStatus } from '../../types/admission';

const store = useAdmissionStore();
const auth = useAuthStore();

const statusFilter = ref<AdmissionStatus | ''>('');

const tabs: { label: string; value: AdmissionStatus | '' }[] = [
    { label: 'All', value: '' },
    { label: 'Draft', value: 'draft' },
    { label: 'Processing', value: 'processing' },
    { label: 'Awaiting medical', value: 'awaiting_medical' },
    { label: 'Awaiting housing', value: 'awaiting_housing' },
    { label: 'Completed', value: 'completed' },
];

function load(): void {
    store.fetchList(statusFilter.value);
}

function setFilter(value: AdmissionStatus | ''): void {
    statusFilter.value = value;
    load();
}

function formatDate(value: string): string {
    return new Date(value).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
}

onMounted(load);
</script>

<template>
    <DashboardLayout>
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-slate-900">Admissions</h1>
            <router-link
                v-if="auth.hasRole('officer', 'admin')"
                :to="{ name: 'admissions.create' }"
                class="btn-primary-sm"
            >
                Start admission
            </router-link>
        </div>

        <div class="mt-4 flex flex-wrap gap-2">
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

        <div class="mt-6 surface-shell">
            <router-link
                v-for="admission in store.admissions"
                :key="admission.id"
                :to="{ name: 'admissions.show', params: { id: admission.id } }"
                class="table-row flex items-center justify-between px-4 py-3"
            >
                <div>
                    <p class="text-sm font-medium text-slate-900">{{ admission.prisoner_name }}</p>
                    <p class="text-xs text-slate-400">{{ admission.admission_reason }} · {{ formatDate(admission.admission_date) }}</p>
                </div>
                <StatusBadge :status="admission.status" />
            </router-link>

            <p v-if="!store.loading && store.admissions.length === 0" class="px-4 py-6 text-sm text-slate-500">No admissions found.</p>
        </div>
    </DashboardLayout>
</template>

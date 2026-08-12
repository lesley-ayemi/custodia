<script setup lang="ts">
import { onMounted } from 'vue';
import { useRouter } from 'vue-router';
import DashboardLayout from '../../layouts/DashboardLayout.vue';
import StatusBadge from '../../components/StatusBadge.vue';
import DataTable from '../../components/DataTable.vue';
import type { DataTableColumn } from '../../components/DataTable.vue';
import { useCourtStore } from '../../stores/court';
import type { CourtHearing } from '../../types/court';

const store = useCourtStore();
const router = useRouter();

const columns: DataTableColumn[] = [
    { key: 'case_number', label: 'Case #', sortable: true },
    { key: 'prisoner_name', label: 'Prisoner', sortable: true },
    { key: 'type', label: 'Type' },
    { key: 'scheduled_at', label: 'Scheduled', sortable: true },
    { key: 'location', label: 'Location' },
    { key: 'status', label: 'Status' },
];

function formatDateTime(value: string): string {
    return new Date(value).toLocaleString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function openPrisoner(row: Record<string, unknown>): void {
    router.push({ name: 'prisoners.show', params: { id: (row as unknown as CourtHearing).prisoner_id! } });
}

onMounted(() => store.fetchUpcomingHearings());
</script>

<template>
    <DashboardLayout>
        <h1 class="text-2xl font-bold text-slate-900">Upcoming hearings</h1>

        <div class="mt-4">
            <DataTable
                :columns="columns"
                :rows="store.upcomingHearings as unknown as Record<string, unknown>[]"
                :loading="store.loading"
                empty-message="No upcoming hearings scheduled."
                searchable
                search-placeholder="Search hearings…"
                clickable-rows
                @row-click="openPrisoner"
            >
                <template #cell-case_number="{ value }">
                    <span class="font-medium text-slate-900">{{ value }}</span>
                </template>
                <template #cell-type="{ value }">
                    <span class="text-slate-500 capitalize">{{ value }}</span>
                </template>
                <template #cell-scheduled_at="{ value }">
                    <span class="text-slate-500">{{ formatDateTime(value as string) }}</span>
                </template>
                <template #cell-location="{ value }">
                    <span class="text-slate-500">{{ value }}</span>
                </template>
                <template #cell-status="{ row }">
                    <StatusBadge :status="(row as unknown as CourtHearing).status" />
                </template>
            </DataTable>
        </div>
    </DashboardLayout>
</template>

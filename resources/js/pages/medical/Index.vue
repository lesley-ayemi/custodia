<script setup lang="ts">
import { onMounted } from 'vue';
import { useRouter } from 'vue-router';
import DashboardLayout from '../../layouts/DashboardLayout.vue';
import DataTable from '../../components/DataTable.vue';
import type { DataTableColumn } from '../../components/DataTable.vue';
import { useMedicalStore } from '../../stores/medical';
import type { MedicalAppointment } from '../../types/medical';

const store = useMedicalStore();
const router = useRouter();

const columns: DataTableColumn[] = [
    { key: 'prisoner_name', label: 'Prisoner', sortable: true },
    { key: 'appointment_type', label: 'Type' },
    { key: 'scheduled_at', label: 'Scheduled', sortable: true },
    { key: 'location', label: 'Location' },
    { key: 'provider', label: 'Provider' },
];

function formatDateTime(value: string): string {
    return new Date(value).toLocaleString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function openPrisoner(row: Record<string, unknown>): void {
    router.push({ name: 'prisoners.show', params: { id: (row as unknown as MedicalAppointment).prisoner_id } });
}

onMounted(() => store.fetchUpcomingAppointments());
</script>

<template>
    <DashboardLayout>
        <h1 class="text-2xl font-bold text-slate-900">Upcoming appointments</h1>

        <div class="mt-4">
            <DataTable
                :columns="columns"
                :rows="store.upcomingAppointments as unknown as Record<string, unknown>[]"
                :loading="store.loading"
                empty-message="No upcoming appointments scheduled."
                searchable
                search-placeholder="Search appointments…"
                clickable-rows
                @row-click="openPrisoner"
            >
                <template #cell-prisoner_name="{ value }">
                    <span class="font-medium text-slate-900">{{ value }}</span>
                </template>
                <template #cell-appointment_type="{ value }">
                    <span class="text-slate-500">{{ value }}</span>
                </template>
                <template #cell-scheduled_at="{ value }">
                    <span class="text-slate-500">{{ formatDateTime(value as string) }}</span>
                </template>
                <template #cell-location="{ value }">
                    <span class="text-slate-500">{{ value }}</span>
                </template>
                <template #cell-provider="{ value }">
                    <span class="text-slate-500">{{ value ?? '—' }}</span>
                </template>
            </DataTable>
        </div>
    </DashboardLayout>
</template>

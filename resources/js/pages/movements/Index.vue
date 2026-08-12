<script setup lang="ts">
import { onMounted, ref } from 'vue';
import DashboardLayout from '../../layouts/DashboardLayout.vue';
import StatusBadge from '../../components/StatusBadge.vue';
import DataTable from '../../components/DataTable.vue';
import type { DataTableColumn } from '../../components/DataTable.vue';
import { useMovementStore } from '../../stores/movement';
import { useAuthStore } from '../../stores/auth';
import type { Movement } from '../../types/movement';

const store = useMovementStore();
const auth = useAuthStore();
const busyKey = ref<string | null>(null);

const columns: DataTableColumn[] = [
    { key: 'prisoner_name', label: 'Prisoner', sortable: true },
    { key: 'route', label: 'Route' },
    { key: 'reason', label: 'Reason' },
    { key: 'scheduled_at', label: 'Scheduled', sortable: true },
    { key: 'status', label: 'Status' },
    { key: 'actions', label: 'Actions' },
];

function formatDateTime(value: string): string {
    return new Date(value).toLocaleString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

async function load(): Promise<void> {
    await store.fetchUpcoming();
}

async function act(action: 'approve' | 'depart' | 'arrive' | 'markReturned' | 'cancel', id: number): Promise<void> {
    busyKey.value = `${action}-${id}`;

    try {
        await store[action](id);
        await load();
    } finally {
        busyKey.value = null;
    }
}

onMounted(load);
</script>

<template>
    <DashboardLayout>
        <h1 class="text-2xl font-bold text-slate-900">Movements</h1>

        <div class="mt-4">
            <DataTable
                :columns="columns"
                :rows="store.upcoming as unknown as Record<string, unknown>[]"
                :loading="store.loading"
                empty-message="No active movements."
            >
                <template #cell-prisoner_name="{ row }">
                    <router-link
                        :to="{ name: 'prisoners.show', params: { id: (row as unknown as Movement).prisoner_id } }"
                        class="font-medium text-slate-900 hover:underline"
                    >
                        {{ (row as unknown as Movement).prisoner_name }}
                    </router-link>
                </template>
                <template #cell-route="{ row }">
                    <span class="text-slate-500">
                        {{ (row as unknown as Movement).from_location }} → {{ (row as unknown as Movement).to_location }}
                    </span>
                </template>
                <template #cell-reason="{ value }">
                    <span class="text-slate-500">{{ value }}</span>
                </template>
                <template #cell-scheduled_at="{ value }">
                    <span class="text-slate-500">{{ formatDateTime(value as string) }}</span>
                </template>
                <template #cell-status="{ row }">
                    <StatusBadge :status="(row as unknown as Movement).status" />
                </template>
                <template #cell-actions="{ row }">
                    <div class="space-x-2 text-xs">
                        <button
                            v-if="(row as unknown as Movement).status === 'requested' && auth.hasRole('supervisor', 'admin')"
                            type="button"
                            :disabled="busyKey === `approve-${(row as unknown as Movement).id}`"
                            class="font-medium text-emerald-700 hover:underline disabled:opacity-50"
                            @click="act('approve', (row as unknown as Movement).id)"
                        >
                            Approve
                        </button>
                        <button
                            v-if="(row as unknown as Movement).status === 'approved' && auth.hasRole('officer', 'admin')"
                            type="button"
                            :disabled="busyKey === `depart-${(row as unknown as Movement).id}`"
                            class="font-medium text-emerald-700 hover:underline disabled:opacity-50"
                            @click="act('depart', (row as unknown as Movement).id)"
                        >
                            Mark departed
                        </button>
                        <button
                            v-if="(row as unknown as Movement).status === 'departed' && auth.hasRole('officer', 'admin')"
                            type="button"
                            :disabled="busyKey === `arrive-${(row as unknown as Movement).id}`"
                            class="font-medium text-emerald-700 hover:underline disabled:opacity-50"
                            @click="act('arrive', (row as unknown as Movement).id)"
                        >
                            Mark arrived
                        </button>
                        <button
                            v-if="(row as unknown as Movement).status === 'arrived' && auth.hasRole('officer', 'admin')"
                            type="button"
                            :disabled="busyKey === `markReturned-${(row as unknown as Movement).id}`"
                            class="font-medium text-emerald-700 hover:underline disabled:opacity-50"
                            @click="act('markReturned', (row as unknown as Movement).id)"
                        >
                            Mark returned
                        </button>
                    </div>
                </template>
            </DataTable>
        </div>
    </DashboardLayout>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import DashboardLayout from '../../layouts/DashboardLayout.vue';
import StatusBadge from '../../components/StatusBadge.vue';
import { useMovementStore } from '../../stores/movement';
import { useAuthStore } from '../../stores/auth';

const store = useMovementStore();
const auth = useAuthStore();
const busyKey = ref<string | null>(null);

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
        <h1 class="text-xl font-semibold text-slate-900">Movements</h1>

        <div class="mt-4 overflow-hidden rounded-lg border border-slate-200 bg-white">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-medium tracking-wider text-slate-500 uppercase">
                    <tr>
                        <th class="px-4 py-3">Prisoner</th>
                        <th class="px-4 py-3">Route</th>
                        <th class="px-4 py-3">Reason</th>
                        <th class="px-4 py-3">Scheduled</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="movement in store.upcoming" :key="movement.id">
                        <td class="px-4 py-3 font-medium text-slate-900">
                            <router-link :to="{ name: 'prisoners.show', params: { id: movement.prisoner_id } }" class="hover:underline">
                                {{ movement.prisoner_name }}
                            </router-link>
                        </td>
                        <td class="px-4 py-3 text-slate-500">{{ movement.from_location }} → {{ movement.to_location }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ movement.reason }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ formatDateTime(movement.scheduled_at) }}</td>
                        <td class="px-4 py-3"><StatusBadge :status="movement.status" /></td>
                        <td class="space-x-2 px-4 py-3 text-xs">
                            <button
                                v-if="movement.status === 'requested' && auth.hasRole('supervisor', 'admin')"
                                type="button"
                                :disabled="busyKey === `approve-${movement.id}`"
                                class="font-medium text-emerald-700 hover:underline disabled:opacity-50"
                                @click="act('approve', movement.id)"
                            >
                                Approve
                            </button>
                            <button
                                v-if="movement.status === 'approved' && auth.hasRole('officer', 'admin')"
                                type="button"
                                :disabled="busyKey === `depart-${movement.id}`"
                                class="font-medium text-emerald-700 hover:underline disabled:opacity-50"
                                @click="act('depart', movement.id)"
                            >
                                Mark departed
                            </button>
                            <button
                                v-if="movement.status === 'departed' && auth.hasRole('officer', 'admin')"
                                type="button"
                                :disabled="busyKey === `arrive-${movement.id}`"
                                class="font-medium text-emerald-700 hover:underline disabled:opacity-50"
                                @click="act('arrive', movement.id)"
                            >
                                Mark arrived
                            </button>
                            <button
                                v-if="movement.status === 'arrived' && auth.hasRole('officer', 'admin')"
                                type="button"
                                :disabled="busyKey === `markReturned-${movement.id}`"
                                class="font-medium text-emerald-700 hover:underline disabled:opacity-50"
                                @click="act('markReturned', movement.id)"
                            >
                                Mark returned
                            </button>
                        </td>
                    </tr>
                    <tr v-if="!store.loading && store.upcoming.length === 0">
                        <td colspan="6" class="px-4 py-6 text-center text-slate-500">No active movements.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </DashboardLayout>
</template>

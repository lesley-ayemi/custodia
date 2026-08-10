<script setup lang="ts">
import { onMounted } from 'vue';
import DashboardLayout from '../layouts/DashboardLayout.vue';
import StatCard from '../components/StatCard.vue';
import StatusBadge from '../components/StatusBadge.vue';
import OccupancyBar from '../components/OccupancyBar.vue';
import { useDashboardStore } from '../stores/dashboard';

const store = useDashboardStore();

function formatDate(value: string): string {
    return new Date(value).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
}

onMounted(() => store.fetchDashboard());
</script>

<template>
    <DashboardLayout>
        <h1 class="text-xl font-semibold text-slate-900">Dashboard</h1>

        <div v-if="store.data" class="mt-6">
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <StatCard label="Prisoners" :value="store.data.total_prisoners" />
                <StatCard label="Occupancy" :value="`${store.data.occupancy_percent}%`" />
                <StatCard label="Open Incidents" :value="store.data.open_incidents" />
                <StatCard label="Available Beds" :value="store.data.available_beds" />
            </div>

            <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="rounded-lg border border-slate-200 bg-white p-6">
                    <h2 class="text-sm font-semibold text-slate-700">Recent incidents</h2>
                    <table class="mt-4 min-w-full text-sm">
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="incident in store.data.recent_incidents" :key="incident.id">
                                <td class="py-2 font-medium text-slate-900">{{ incident.incident_number }}</td>
                                <td class="py-2 text-slate-600 capitalize">{{ incident.type.replaceAll('_', ' ') }}</td>
                                <td class="py-2 text-slate-400">{{ formatDate(incident.occurred_at) }}</td>
                                <td class="py-2"><StatusBadge :status="incident.status" /></td>
                            </tr>
                            <tr v-if="store.data.recent_incidents.length === 0">
                                <td colspan="4" class="py-4 text-center text-slate-500">No incidents recorded.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="rounded-lg border border-slate-200 bg-white p-6">
                    <h2 class="text-sm font-semibold text-slate-700">Prison occupancy</h2>
                    <div class="mt-4 space-y-4">
                        <div v-for="block in store.data.block_occupancy" :key="block.name">
                            <div class="mb-1 flex justify-between text-sm">
                                <span class="text-slate-700">{{ block.name }}</span>
                                <span class="text-slate-500">{{ block.percent }}%</span>
                            </div>
                            <OccupancyBar :occupied="block.percent" :capacity="100" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

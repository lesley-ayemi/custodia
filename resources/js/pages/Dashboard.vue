<script setup lang="ts">
import { onMounted } from 'vue';
import DashboardLayout from '../layouts/DashboardLayout.vue';
import StatCard from '../components/StatCard.vue';
import StatusBadge from '../components/StatusBadge.vue';
import OccupancyBar from '../components/OccupancyBar.vue';
import { useDashboardStore } from '../stores/dashboard';
import { Users, BedDouble, AlertTriangle, DoorOpen } from '@lucide/vue';

const store = useDashboardStore();

function formatDate(value: string): string {
    return new Date(value).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
}

onMounted(() => store.fetchDashboard());
</script>

<template>
    <DashboardLayout>
        <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
        <p class="mt-1 text-sm text-slate-500">A snapshot of the facility right now.</p>

        <div v-if="store.data" class="mt-6">
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <StatCard
                    label="Prisoners"
                    :value="store.data.total_prisoners"
                    :icon="Users"
                    icon-class="bg-primary-600"
                    :to="{ name: 'prisoners.index' }"
                    link-label="View all prisoners"
                />
                <StatCard
                    label="Occupancy"
                    :value="`${store.data.occupancy_percent}%`"
                    :icon="BedDouble"
                    icon-class="bg-blue-500"
                    :to="{ name: 'housing.index' }"
                    link-label="View housing"
                />
                <StatCard
                    label="Open Incidents"
                    :value="store.data.open_incidents"
                    :icon="AlertTriangle"
                    icon-class="bg-amber-500"
                    :to="{ name: 'incidents.index' }"
                    link-label="View incidents"
                />
                <StatCard
                    label="Available Beds"
                    :value="store.data.available_beds"
                    :icon="DoorOpen"
                    icon-class="bg-emerald-500"
                    :to="{ name: 'housing.index' }"
                    link-label="View housing"
                />
            </div>

            <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="surface-card">
                    <h2 class="text-sm font-semibold text-slate-700">Recent incidents</h2>
                    <div class="mt-3 -mx-6">
                        <table class="w-full text-sm">
                            <tbody>
                                <tr v-for="incident in store.data.recent_incidents" :key="incident.id" class="table-row">
                                    <td class="px-6 py-3 font-semibold text-slate-900">{{ incident.incident_number }}</td>
                                    <td class="px-3 py-3 text-slate-600 capitalize">{{ incident.type.replaceAll('_', ' ') }}</td>
                                    <td class="px-3 py-3 text-slate-400">{{ formatDate(incident.occurred_at) }}</td>
                                    <td class="px-6 py-3 text-right"><StatusBadge :status="incident.status" /></td>
                                </tr>
                                <tr v-if="store.data.recent_incidents.length === 0">
                                    <td colspan="4" class="px-6 py-6 text-center text-slate-500">No incidents recorded.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="surface-card">
                    <h2 class="text-sm font-semibold text-slate-700">Prison occupancy</h2>
                    <div class="mt-4 space-y-4">
                        <div v-for="block in store.data.block_occupancy" :key="block.name">
                            <div class="mb-1.5 flex justify-between text-sm">
                                <span class="font-medium text-slate-700">{{ block.name }}</span>
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

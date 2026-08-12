<script setup lang="ts">
import { onMounted } from 'vue';
import DashboardLayout from '../../layouts/DashboardLayout.vue';
import StatusBadge from '../../components/StatusBadge.vue';
import { useCourtStore } from '../../stores/court';

const store = useCourtStore();

function formatDateTime(value: string): string {
    return new Date(value).toLocaleString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

onMounted(() => store.fetchUpcomingHearings());
</script>

<template>
    <DashboardLayout>
        <h1 class="text-2xl font-bold text-slate-900">Upcoming hearings</h1>

        <div class="mt-4 surface-shell">
            <table class="w-full text-sm">
                <thead class="border-b border-slate-100 bg-slate-50/60 text-left">
                    <tr>
                        <th class="table-header-cell">Case #</th>
                        <th class="table-header-cell">Prisoner</th>
                        <th class="table-header-cell">Type</th>
                        <th class="table-header-cell">Scheduled</th>
                        <th class="table-header-cell">Location</th>
                        <th class="table-header-cell">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="hearing in store.upcomingHearings"
                        :key="hearing.id"
                        class="table-row cursor-pointer"
                        @click="$router.push({ name: 'prisoners.show', params: { id: hearing.prisoner_id } })"
                    >
                        <td class="px-4 py-3 font-medium text-slate-900">{{ hearing.case_number }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ hearing.prisoner_name }}</td>
                        <td class="px-4 py-3 text-slate-500 capitalize">{{ hearing.type }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ formatDateTime(hearing.scheduled_at) }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ hearing.location }}</td>
                        <td class="px-4 py-3"><StatusBadge :status="hearing.status" /></td>
                    </tr>
                    <tr v-if="!store.loading && store.upcomingHearings.length === 0">
                        <td colspan="6" class="px-4 py-6 text-center text-slate-500">No upcoming hearings scheduled.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </DashboardLayout>
</template>

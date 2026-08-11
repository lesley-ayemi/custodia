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
        <h1 class="text-xl font-semibold text-slate-900">Upcoming hearings</h1>

        <div class="mt-4 overflow-hidden rounded-lg border border-slate-200 bg-white">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-medium tracking-wider text-slate-500 uppercase">
                    <tr>
                        <th class="px-4 py-3">Case #</th>
                        <th class="px-4 py-3">Prisoner</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Scheduled</th>
                        <th class="px-4 py-3">Location</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr
                        v-for="hearing in store.upcomingHearings"
                        :key="hearing.id"
                        class="cursor-pointer hover:bg-slate-50"
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

<script setup lang="ts">
import { onMounted } from 'vue';
import DashboardLayout from '../../layouts/DashboardLayout.vue';
import { useMedicalStore } from '../../stores/medical';

const store = useMedicalStore();

function formatDateTime(value: string): string {
    return new Date(value).toLocaleString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

onMounted(() => store.fetchUpcomingAppointments());
</script>

<template>
    <DashboardLayout>
        <h1 class="text-xl font-semibold text-slate-900">Upcoming appointments</h1>

        <div class="mt-4 overflow-hidden rounded-lg border border-slate-200 bg-white">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-medium tracking-wider text-slate-500 uppercase">
                    <tr>
                        <th class="px-4 py-3">Prisoner</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Scheduled</th>
                        <th class="px-4 py-3">Location</th>
                        <th class="px-4 py-3">Provider</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr
                        v-for="appointment in store.upcomingAppointments"
                        :key="appointment.id"
                        class="cursor-pointer hover:bg-slate-50"
                        @click="$router.push({ name: 'prisoners.show', params: { id: appointment.prisoner_id } })"
                    >
                        <td class="px-4 py-3 font-medium text-slate-900">{{ appointment.prisoner_name }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ appointment.appointment_type }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ formatDateTime(appointment.scheduled_at) }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ appointment.location }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ appointment.provider ?? '—' }}</td>
                    </tr>
                    <tr v-if="!store.loading && store.upcomingAppointments.length === 0">
                        <td colspan="5" class="px-4 py-6 text-center text-slate-500">No upcoming appointments scheduled.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </DashboardLayout>
</template>

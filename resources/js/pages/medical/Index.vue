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
        <h1 class="text-2xl font-bold text-slate-900">Upcoming appointments</h1>

        <div class="mt-4 surface-shell">
            <table class="w-full text-sm">
                <thead class="border-b border-slate-100 bg-slate-50/60 text-left">
                    <tr>
                        <th class="table-header-cell">Prisoner</th>
                        <th class="table-header-cell">Type</th>
                        <th class="table-header-cell">Scheduled</th>
                        <th class="table-header-cell">Location</th>
                        <th class="table-header-cell">Provider</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="appointment in store.upcomingAppointments"
                        :key="appointment.id"
                        class="table-row cursor-pointer"
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

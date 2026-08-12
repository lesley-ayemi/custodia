<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import StatusBadge from './StatusBadge.vue';
import { useMedicalStore } from '../stores/medical';
import type { MedicalAppointmentFormData, MedicalRecordFormData, PrescriptionFormData } from '../types/medical';

const props = defineProps<{ prisonerId: number }>();

const store = useMedicalStore();

const showRecordForm = ref(false);
const recordForm = reactive<MedicalRecordFormData>({ condition: '', notes: '' });

const showAppointmentForm = ref(false);
const appointmentForm = reactive<MedicalAppointmentFormData>({
    appointment_type: '',
    provider: '',
    location: '',
    scheduled_at: '',
    notes: '',
});

const showPrescriptionForm = ref(false);
const prescriptionForm = reactive<PrescriptionFormData>({
    medication_name: '',
    dosage: '',
    frequency: '',
    administration_time: '',
    start_date: new Date().toISOString().slice(0, 10),
    notes: '',
});

const busyKey = ref<string | null>(null);

function formatDate(value: string): string {
    return new Date(value).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
}

function formatDateTime(value: string): string {
    return new Date(value).toLocaleString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

async function load(): Promise<void> {
    await Promise.all([
        store.fetchRecords(props.prisonerId),
        store.fetchAppointments(props.prisonerId),
        store.fetchPrescriptions(props.prisonerId),
    ]);
}

async function submitRecord(): Promise<void> {
    busyKey.value = 'record';
    try {
        await store.addRecord(props.prisonerId, recordForm);
        recordForm.condition = '';
        recordForm.notes = '';
        showRecordForm.value = false;
        await load();
    } finally {
        busyKey.value = null;
    }
}

async function submitAppointment(): Promise<void> {
    busyKey.value = 'appointment';
    try {
        await store.scheduleAppointment(props.prisonerId, appointmentForm);
        appointmentForm.appointment_type = '';
        appointmentForm.provider = '';
        appointmentForm.location = '';
        appointmentForm.scheduled_at = '';
        appointmentForm.notes = '';
        showAppointmentForm.value = false;
        await load();
    } finally {
        busyKey.value = null;
    }
}

async function completeAppointment(id: number): Promise<void> {
    busyKey.value = `complete-${id}`;
    try {
        await store.completeAppointment(id);
        await load();
    } finally {
        busyKey.value = null;
    }
}

async function cancelAppointment(id: number): Promise<void> {
    busyKey.value = `cancel-${id}`;
    try {
        await store.cancelAppointment(id);
        await load();
    } finally {
        busyKey.value = null;
    }
}

async function submitPrescription(): Promise<void> {
    busyKey.value = 'prescription';
    try {
        await store.prescribe(props.prisonerId, prescriptionForm);
        prescriptionForm.medication_name = '';
        prescriptionForm.dosage = '';
        prescriptionForm.frequency = '';
        prescriptionForm.administration_time = '';
        prescriptionForm.notes = '';
        showPrescriptionForm.value = false;
        await load();
    } finally {
        busyKey.value = null;
    }
}

async function discontinuePrescription(id: number): Promise<void> {
    busyKey.value = `discontinue-${id}`;
    try {
        await store.discontinuePrescription(id);
        await load();
    } finally {
        busyKey.value = null;
    }
}

onMounted(load);
</script>

<template>
    <div class="space-y-6">
        <div class="rounded-lg border border-slate-200 bg-white p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-700">Medical records</h2>
                <button type="button" class="text-sm font-medium text-slate-900 hover:underline" @click="showRecordForm = !showRecordForm">
                    {{ showRecordForm ? 'Cancel' : '+ Add record' }}
                </button>
            </div>

            <form v-if="showRecordForm" class="mt-4 space-y-2 rounded-md border border-slate-200 bg-slate-50 p-4" @submit.prevent="submitRecord">
                <div>
                    <label class="block text-xs font-medium text-slate-600">Condition</label>
                    <input
                        v-model="recordForm.condition"
                        type="text"
                        required
                        placeholder="Type 2 Diabetes"
                        class="mt-1 block w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm"
                    />
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600">Clinical notes</label>
                    <textarea
                        v-model="recordForm.notes"
                        rows="2"
                        class="mt-1 block w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm"
                    ></textarea>
                </div>
                <button
                    type="submit"
                    :disabled="busyKey === 'record'"
                    class="rounded-md bg-slate-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-slate-800 disabled:opacity-50"
                >
                    {{ busyKey === 'record' ? 'Saving…' : 'Add record' }}
                </button>
            </form>

            <ul class="mt-4 space-y-2">
                <li v-for="record in store.records" :key="record.id" class="rounded-md border border-slate-200 p-3 text-sm">
                    <p class="font-medium text-slate-900">{{ record.condition }}</p>
                    <p v-if="record.notes" class="mt-1 text-xs text-slate-500">{{ record.notes }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ record.recorded_by }} · {{ formatDate(record.recorded_at) }}</p>
                </li>
                <p v-if="!store.loading && store.records.length === 0" class="text-sm text-slate-500">No medical records on file.</p>
            </ul>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-700">Appointments</h2>
                <button type="button" class="text-sm font-medium text-slate-900 hover:underline" @click="showAppointmentForm = !showAppointmentForm">
                    {{ showAppointmentForm ? 'Cancel' : '+ Schedule appointment' }}
                </button>
            </div>

            <form
                v-if="showAppointmentForm"
                class="mt-4 space-y-2 rounded-md border border-slate-200 bg-slate-50 p-4"
                @submit.prevent="submitAppointment"
            >
                <div class="grid grid-cols-2 gap-2">
                    <input
                        v-model="appointmentForm.appointment_type"
                        type="text"
                        required
                        placeholder="GP review"
                        class="rounded-md border border-slate-300 px-2 py-1.5 text-sm"
                    />
                    <input
                        v-model="appointmentForm.provider"
                        type="text"
                        placeholder="Provider (optional)"
                        class="rounded-md border border-slate-300 px-2 py-1.5 text-sm"
                    />
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <input
                        v-model="appointmentForm.location"
                        type="text"
                        required
                        placeholder="Health Wing"
                        class="rounded-md border border-slate-300 px-2 py-1.5 text-sm"
                    />
                    <input
                        v-model="appointmentForm.scheduled_at"
                        type="datetime-local"
                        required
                        class="rounded-md border border-slate-300 px-2 py-1.5 text-sm"
                    />
                </div>
                <textarea
                    v-model="appointmentForm.notes"
                    rows="2"
                    placeholder="Notes (optional)"
                    class="block w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm"
                ></textarea>
                <button
                    type="submit"
                    :disabled="busyKey === 'appointment'"
                    class="rounded-md bg-slate-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-slate-800 disabled:opacity-50"
                >
                    {{ busyKey === 'appointment' ? 'Scheduling…' : 'Schedule' }}
                </button>
            </form>

            <ul class="mt-4 space-y-2">
                <li v-for="appointment in store.appointments" :key="appointment.id" class="rounded-md border border-slate-200 p-3 text-sm">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="font-medium text-slate-900">{{ appointment.appointment_type }}</p>
                            <p class="text-xs text-slate-500">
                                {{ formatDateTime(appointment.scheduled_at) }} · {{ appointment.location }}
                                <span v-if="appointment.provider"> · {{ appointment.provider }}</span>
                            </p>
                        </div>
                        <StatusBadge :status="appointment.status" />
                    </div>
                    <div v-if="appointment.status === 'scheduled'" class="mt-2 flex gap-3 text-xs">
                        <button
                            type="button"
                            :disabled="busyKey === `complete-${appointment.id}`"
                            class="font-medium text-emerald-700 hover:underline disabled:opacity-50"
                            @click="completeAppointment(appointment.id)"
                        >
                            Mark completed
                        </button>
                        <button
                            type="button"
                            :disabled="busyKey === `cancel-${appointment.id}`"
                            class="font-medium text-red-600 hover:underline disabled:opacity-50"
                            @click="cancelAppointment(appointment.id)"
                        >
                            Cancel
                        </button>
                    </div>
                </li>
                <p v-if="!store.loading && store.appointments.length === 0" class="text-sm text-slate-500">No appointments on file.</p>
            </ul>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-700">Prescriptions</h2>
                <button type="button" class="text-sm font-medium text-slate-900 hover:underline" @click="showPrescriptionForm = !showPrescriptionForm">
                    {{ showPrescriptionForm ? 'Cancel' : '+ Prescribe' }}
                </button>
            </div>

            <form
                v-if="showPrescriptionForm"
                class="mt-4 space-y-2 rounded-md border border-slate-200 bg-slate-50 p-4"
                @submit.prevent="submitPrescription"
            >
                <div class="grid grid-cols-2 gap-2">
                    <input
                        v-model="prescriptionForm.medication_name"
                        type="text"
                        required
                        placeholder="Metformin"
                        class="rounded-md border border-slate-300 px-2 py-1.5 text-sm"
                    />
                    <input
                        v-model="prescriptionForm.dosage"
                        type="text"
                        required
                        placeholder="500mg"
                        class="rounded-md border border-slate-300 px-2 py-1.5 text-sm"
                    />
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <input
                        v-model="prescriptionForm.frequency"
                        type="text"
                        required
                        placeholder="Twice daily"
                        class="rounded-md border border-slate-300 px-2 py-1.5 text-sm"
                    />
                    <input
                        v-model="prescriptionForm.administration_time"
                        type="time"
                        placeholder="Time (optional)"
                        class="rounded-md border border-slate-300 px-2 py-1.5 text-sm"
                    />
                    <input
                        v-model="prescriptionForm.start_date"
                        type="date"
                        required
                        class="rounded-md border border-slate-300 px-2 py-1.5 text-sm"
                    />
                </div>
                <textarea
                    v-model="prescriptionForm.notes"
                    rows="2"
                    placeholder="Notes (optional)"
                    class="block w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm"
                ></textarea>
                <button
                    type="submit"
                    :disabled="busyKey === 'prescription'"
                    class="rounded-md bg-slate-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-slate-800 disabled:opacity-50"
                >
                    {{ busyKey === 'prescription' ? 'Saving…' : 'Prescribe' }}
                </button>
            </form>

            <ul class="mt-4 space-y-2">
                <li v-for="prescription in store.prescriptions" :key="prescription.id" class="rounded-md border border-slate-200 p-3 text-sm">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="font-medium text-slate-900">{{ prescription.medication_name }} · {{ prescription.dosage }}</p>
                            <p class="text-xs text-slate-500">
                                {{ prescription.frequency }}
                                <span v-if="prescription.administration_time"> at {{ prescription.administration_time.slice(0, 5) }}</span>
                            </p>
                            <p class="mt-1 text-xs text-slate-400">
                                From {{ formatDate(prescription.start_date) }}
                                <span v-if="prescription.end_date"> to {{ formatDate(prescription.end_date) }}</span>
                            </p>
                        </div>
                        <StatusBadge :status="prescription.status" />
                    </div>
                    <button
                        v-if="prescription.status === 'active'"
                        type="button"
                        :disabled="busyKey === `discontinue-${prescription.id}`"
                        class="mt-2 text-xs font-medium text-red-600 hover:underline disabled:opacity-50"
                        @click="discontinuePrescription(prescription.id)"
                    >
                        Discontinue
                    </button>
                </li>
                <p v-if="!store.loading && store.prescriptions.length === 0" class="text-sm text-slate-500">No prescriptions on file.</p>
            </ul>
        </div>
    </div>
</template>

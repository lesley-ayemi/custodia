<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import StatusBadge from './StatusBadge.vue';
import { useProgrammeStore } from '../stores/programme';
import { useAuthStore } from '../stores/auth';

const props = defineProps<{ prisonerId: number }>();

const store = useProgrammeStore();
const auth = useAuthStore();

const showEnrolForm = ref(false);
const enrolling = ref(false);
const enrolForm = reactive({ programme_id: null as number | null, enrolled_at: new Date().toISOString().slice(0, 10) });

const attendanceFormOpenFor = ref<number | null>(null);
const recordingAttendance = ref(false);
const attendanceForm = reactive({ session_date: new Date().toISOString().slice(0, 10), attended: true, notes: '' });

const withdrawFormOpenFor = ref<number | null>(null);
const withdrawing = ref(false);
const withdrawReason = ref('');

const completingId = ref<number | null>(null);

function formatDate(value: string): string {
    return new Date(value).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
}

async function load(): Promise<void> {
    await store.fetchEnrolmentsForPrisoner(props.prisonerId);
    await store.fetchProgrammes();
}

async function submitEnrol(): Promise<void> {
    if (!enrolForm.programme_id) return;
    enrolling.value = true;

    try {
        await store.enrol(props.prisonerId, enrolForm.programme_id, enrolForm.enrolled_at);
        showEnrolForm.value = false;
        enrolForm.programme_id = null;
        await load();
    } finally {
        enrolling.value = false;
    }
}

function toggleAttendanceForm(enrolmentId: number): void {
    attendanceFormOpenFor.value = attendanceFormOpenFor.value === enrolmentId ? null : enrolmentId;
    attendanceForm.session_date = new Date().toISOString().slice(0, 10);
    attendanceForm.attended = true;
    attendanceForm.notes = '';
}

async function submitAttendance(enrolmentId: number): Promise<void> {
    recordingAttendance.value = true;

    try {
        await store.recordAttendance(enrolmentId, { ...attendanceForm, notes: attendanceForm.notes || null });
        attendanceFormOpenFor.value = null;
        await load();
    } finally {
        recordingAttendance.value = false;
    }
}

async function markComplete(enrolmentId: number): Promise<void> {
    completingId.value = enrolmentId;

    try {
        await store.complete(enrolmentId);
        await load();
    } finally {
        completingId.value = null;
    }
}

function toggleWithdrawForm(enrolmentId: number): void {
    withdrawFormOpenFor.value = withdrawFormOpenFor.value === enrolmentId ? null : enrolmentId;
    withdrawReason.value = '';
}

async function submitWithdraw(enrolmentId: number): Promise<void> {
    withdrawing.value = true;

    try {
        await store.withdraw(enrolmentId, withdrawReason.value || null);
        withdrawFormOpenFor.value = null;
        await load();
    } finally {
        withdrawing.value = false;
    }
}

onMounted(load);
</script>

<template>
    <div class="surface-card">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-700">Programmes</h2>
            <button
                v-if="auth.hasRole('officer', 'admin')"
                type="button"
                class="text-sm font-medium text-primary-600 hover:underline"
                @click="showEnrolForm = !showEnrolForm"
            >
                {{ showEnrolForm ? 'Cancel' : '+ Enrol in programme' }}
            </button>
        </div>

        <form v-if="showEnrolForm" class="mt-4 flex items-end gap-2 rounded-xl border border-slate-100 bg-slate-50/60 p-4" @submit.prevent="submitEnrol">
            <div class="flex-1">
                <label class="field-label">Programme</label>
                <select v-model="enrolForm.programme_id" required class="mt-1 field-input-sm">
                    <option :value="null" disabled>Select a programme…</option>
                    <option v-for="programme in store.programmes" :key="programme.id" :value="programme.id">{{ programme.name }}</option>
                </select>
            </div>
            <div>
                <label class="field-label">Enrolled</label>
                <input
                    v-model="enrolForm.enrolled_at"
                    type="date"
                    required
                    class="mt-1 field-input-sm"
                />
            </div>
            <button
                type="submit"
                :disabled="enrolling"
                class="btn-primary-sm"
            >
                {{ enrolling ? 'Enrolling…' : 'Enrol' }}
            </button>
        </form>

        <div class="mt-4 space-y-4">
            <div v-for="enrolment in store.enrolments" :key="enrolment.id" class="rounded-xl border border-slate-100 bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-900">{{ enrolment.programme_name }}</p>
                        <p class="text-xs text-slate-400">Enrolled {{ formatDate(enrolment.enrolled_at) }}</p>
                        <p v-if="enrolment.session_count > 0" class="mt-1 text-xs text-slate-500">
                            Attendance: {{ enrolment.attended_count }}/{{ enrolment.session_count }} sessions ({{ enrolment.attendance_rate }}%)
                        </p>
                        <p v-if="enrolment.status === 'withdrawn' && enrolment.withdrawal_reason" class="mt-1 text-xs text-slate-500">
                            Reason: {{ enrolment.withdrawal_reason }}
                        </p>
                    </div>
                    <StatusBadge :status="enrolment.status" />
                </div>

                <div v-if="enrolment.status === 'enrolled' && auth.hasRole('officer', 'admin')" class="mt-3 flex items-center gap-3 border-t border-slate-100 pt-3 text-xs">
                    <button type="button" class="font-medium text-slate-700 hover:underline" @click="toggleAttendanceForm(enrolment.id)">
                        {{ attendanceFormOpenFor === enrolment.id ? 'Cancel' : '+ Record attendance' }}
                    </button>
                    <button
                        type="button"
                        :disabled="completingId === enrolment.id"
                        class="font-medium text-emerald-700 hover:underline disabled:opacity-50"
                        @click="markComplete(enrolment.id)"
                    >
                        Mark completed
                    </button>
                    <button type="button" class="font-medium text-red-600 hover:underline" @click="toggleWithdrawForm(enrolment.id)">
                        {{ withdrawFormOpenFor === enrolment.id ? 'Cancel' : 'Withdraw' }}
                    </button>
                </div>

                <form
                    v-if="attendanceFormOpenFor === enrolment.id"
                    class="mt-2 space-y-2 rounded-xl border border-slate-100 bg-slate-50/60 p-3"
                    @submit.prevent="submitAttendance(enrolment.id)"
                >
                    <div class="flex items-end gap-2">
                        <div>
                            <label class="field-label">Session date</label>
                            <input
                                v-model="attendanceForm.session_date"
                                type="date"
                                required
                                class="mt-1 field-input-sm text-xs"
                            />
                        </div>
                        <label class="flex items-center gap-1.5 pb-1.5 text-xs text-slate-600">
                            <input v-model="attendanceForm.attended" type="checkbox" />
                            Attended
                        </label>
                    </div>
                    <input
                        v-model="attendanceForm.notes"
                        type="text"
                        placeholder="Notes (optional)"
                        class="field-input-sm text-xs"
                    />
                    <button
                        type="submit"
                        :disabled="recordingAttendance"
                        class="btn-primary-sm"
                    >
                        {{ recordingAttendance ? 'Saving…' : 'Save' }}
                    </button>
                </form>

                <form
                    v-if="withdrawFormOpenFor === enrolment.id"
                    class="mt-2 flex items-end gap-2 rounded-xl border border-slate-100 bg-slate-50/60 p-3"
                    @submit.prevent="submitWithdraw(enrolment.id)"
                >
                    <div class="flex-1">
                        <label class="field-label">Reason (optional)</label>
                        <input v-model="withdrawReason" type="text" class="mt-1 field-input-sm text-xs" />
                    </div>
                    <button
                        type="submit"
                        :disabled="withdrawing"
                        class="rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-red-700 disabled:opacity-50"
                    >
                        {{ withdrawing ? 'Withdrawing…' : 'Confirm withdraw' }}
                    </button>
                </form>

                <ul v-if="enrolment.attendances.length > 0" class="mt-3 space-y-1 border-t border-slate-100 pt-3">
                    <li v-for="attendance in enrolment.attendances" :key="attendance.id" class="flex items-center justify-between text-xs">
                        <span :class="attendance.attended ? 'text-slate-600' : 'text-slate-400 line-through'">
                            {{ formatDate(attendance.session_date) }}
                            <span v-if="attendance.notes" class="text-slate-400"> · {{ attendance.notes }}</span>
                        </span>
                        <span :class="attendance.attended ? 'text-emerald-600' : 'text-red-500'">{{ attendance.attended ? 'Attended' : 'Missed' }}</span>
                    </li>
                </ul>
            </div>

            <p v-if="!store.loading && store.enrolments.length === 0" class="text-sm text-slate-500">Not enrolled in any programmes.</p>
        </div>
    </div>
</template>

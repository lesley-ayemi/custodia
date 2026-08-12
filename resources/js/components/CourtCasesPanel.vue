<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import StatusBadge from './StatusBadge.vue';
import { useCourtStore } from '../stores/court';
import { useAuthStore } from '../stores/auth';
import type { CourtCaseFormData, CourtHearingFormData } from '../types/court';

const props = defineProps<{ prisonerId: number }>();

const court = useCourtStore();
const auth = useAuthStore();

const showCaseForm = ref(false);
const openingCase = ref(false);
const hearingFormOpenFor = ref<number | null>(null);
const schedulingHearing = ref(false);

const caseForm = reactive<CourtCaseFormData>({
    court_name: '',
    charge: '',
    legal_representative_id: null,
    opened_at: new Date().toISOString().slice(0, 10),
});

const hearingForm = reactive<CourtHearingFormData>({
    type: 'arraignment',
    scheduled_at: '',
    location: '',
    notes: null,
});

function formatDate(value: string): string {
    return new Date(value).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
}

function formatDateTime(value: string): string {
    return new Date(value).toLocaleString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

async function load(): Promise<void> {
    await court.fetchCasesForPrisoner(props.prisonerId);
    await court.fetchLegalRepresentatives();
}

async function submitCase(): Promise<void> {
    openingCase.value = true;

    try {
        await court.openCase(props.prisonerId, caseForm);
        showCaseForm.value = false;
        caseForm.court_name = '';
        caseForm.charge = '';
        caseForm.legal_representative_id = null;
        await load();
    } finally {
        openingCase.value = false;
    }
}

function toggleHearingForm(caseId: number): void {
    hearingFormOpenFor.value = hearingFormOpenFor.value === caseId ? null : caseId;
    hearingForm.scheduled_at = '';
    hearingForm.location = '';
    hearingForm.notes = null;
}

async function submitHearing(caseId: number): Promise<void> {
    schedulingHearing.value = true;

    try {
        await court.scheduleHearing(caseId, hearingForm);
        hearingFormOpenFor.value = null;
        await load();
    } finally {
        schedulingHearing.value = false;
    }
}

onMounted(load);
</script>

<template>
    <div class="surface-card">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-700">Court cases</h2>
            <button
                v-if="auth.hasRole('officer', 'admin')"
                type="button"
                class="text-sm font-medium text-primary-600 hover:underline"
                @click="showCaseForm = !showCaseForm"
            >
                {{ showCaseForm ? 'Cancel' : '+ Open case' }}
            </button>
        </div>

        <form v-if="showCaseForm" class="mt-4 space-y-3 rounded-xl border border-slate-100 bg-slate-50/60 p-4" @submit.prevent="submitCase">
            <div>
                <label class="field-label">Court name</label>
                <input
                    v-model="caseForm.court_name"
                    type="text"
                    required
                    class="mt-1 field-input-sm"
                />
            </div>
            <div>
                <label class="field-label">Charge</label>
                <input
                    v-model="caseForm.charge"
                    type="text"
                    required
                    class="mt-1 field-input-sm"
                />
            </div>
            <div>
                <label class="field-label">Legal representative</label>
                <select v-model="caseForm.legal_representative_id" class="mt-1 field-input-sm">
                    <option :value="null">None assigned</option>
                    <option v-for="rep in court.legalRepresentatives" :key="rep.id" :value="rep.id">
                        {{ rep.name }}<span v-if="rep.firm_name"> — {{ rep.firm_name }}</span>
                    </option>
                </select>
            </div>
            <div>
                <label class="field-label">Opened</label>
                <input
                    v-model="caseForm.opened_at"
                    type="date"
                    required
                    class="mt-1 field-input-sm"
                />
            </div>
            <button
                type="submit"
                :disabled="openingCase"
                class="btn-primary-sm"
            >
                {{ openingCase ? 'Opening…' : 'Open case' }}
            </button>
        </form>

        <div class="mt-4 space-y-4">
            <div v-for="courtCase in court.cases" :key="courtCase.id" class="rounded-xl border border-slate-100 bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-900">{{ courtCase.case_number }}</p>
                        <p class="text-sm text-slate-600">{{ courtCase.court_name }} · {{ courtCase.charge }}</p>
                        <p v-if="courtCase.legal_representative" class="mt-1 text-xs text-slate-500">
                            Counsel: {{ courtCase.legal_representative.name }}
                            <span v-if="courtCase.legal_representative.firm_name">({{ courtCase.legal_representative.firm_name }})</span>
                        </p>
                        <p class="mt-1 text-xs text-slate-400">Opened {{ formatDate(courtCase.opened_at) }}</p>
                    </div>
                    <StatusBadge :status="courtCase.status" />
                </div>

                <div class="mt-3 border-t border-slate-100 pt-3">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Hearings</p>
                        <button
                            v-if="auth.hasRole('officer', 'admin')"
                            type="button"
                            class="text-xs font-medium text-slate-700 hover:underline"
                            @click="toggleHearingForm(courtCase.id)"
                        >
                            {{ hearingFormOpenFor === courtCase.id ? 'Cancel' : '+ Schedule hearing' }}
                        </button>
                    </div>

                    <form
                        v-if="hearingFormOpenFor === courtCase.id"
                        class="mt-2 space-y-2 rounded-xl border border-slate-100 bg-slate-50/60 p-3"
                        @submit.prevent="submitHearing(courtCase.id)"
                    >
                        <div class="grid grid-cols-2 gap-2">
                            <select v-model="hearingForm.type" class="field-input-sm text-xs">
                                <option value="arraignment">Arraignment</option>
                                <option value="bail">Bail</option>
                                <option value="trial">Trial</option>
                                <option value="sentencing">Sentencing</option>
                                <option value="appeal">Appeal</option>
                            </select>
                            <input
                                v-model="hearingForm.location"
                                type="text"
                                required
                                placeholder="Location"
                                class="field-input-sm text-xs"
                            />
                        </div>
                        <input
                            v-model="hearingForm.scheduled_at"
                            type="datetime-local"
                            required
                            class="field-input-sm text-xs"
                        />
                        <button
                            type="submit"
                            :disabled="schedulingHearing"
                            class="btn-primary-sm"
                        >
                            {{ schedulingHearing ? 'Scheduling…' : 'Schedule' }}
                        </button>
                    </form>

                    <ul class="mt-2 space-y-1">
                        <li v-for="hearing in courtCase.hearings" :key="hearing.id" class="flex items-center justify-between text-xs">
                            <span class="text-slate-600 capitalize">{{ hearing.type }} · {{ formatDateTime(hearing.scheduled_at) }} · {{ hearing.location }}</span>
                            <StatusBadge :status="hearing.status" />
                        </li>
                        <li v-if="courtCase.hearings.length === 0" class="text-xs text-slate-400">No hearings scheduled.</li>
                    </ul>
                </div>
            </div>

            <p v-if="!court.loading && court.cases.length === 0" class="text-sm text-slate-500">No court cases on file.</p>
        </div>
    </div>
</template>

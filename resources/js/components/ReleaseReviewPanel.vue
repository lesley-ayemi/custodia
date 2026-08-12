<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import StatusBadge from './StatusBadge.vue';
import { useReleaseStore } from '../stores/release';
import { useAuthStore } from '../stores/auth';
import { RELEASE_STEPS } from '../types/release';
import type { ReleaseStepName } from '../types/release';

const props = defineProps<{ prisonerId: number; prisonerStatus: string }>();
const emit = defineEmits<{ released: [] }>();

const store = useReleaseStore();
const auth = useAuthStore();

const scheduling = ref(false);
const actingStep = ref<ReleaseStepName | null>(null);
const notesDraft = ref('');
const showCancelForm = ref(false);
const cancelling = ref(false);
const cancelReason = ref('');

const activeReview = computed(() => store.prisonerReviews.find((r) => r.status === 'in_progress') ?? null);
const latestReview = computed(() => store.prisonerReviews[0] ?? null);

function formatDate(value: string): string {
    return new Date(value).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
}

function stepEntry(stepValue: ReleaseStepName) {
    return activeReview.value?.steps.find((s) => s.step === stepValue) ?? null;
}

function canActOnStep(gate: 'operational' | 'supervisor'): boolean {
    return gate === 'operational' ? auth.hasRole('officer', 'admin') : auth.hasRole('supervisor', 'admin');
}

async function load(): Promise<void> {
    await store.fetchForPrisoner(props.prisonerId);
}

async function schedule(): Promise<void> {
    scheduling.value = true;

    try {
        await store.schedule(props.prisonerId);
        await load();
    } finally {
        scheduling.value = false;
    }
}

async function submitStep(step: ReleaseStepName, endpoint: string): Promise<void> {
    if (!activeReview.value) return;
    actingStep.value = step;

    try {
        const updated = await store.completeStep(activeReview.value.id, endpoint, notesDraft.value || null);
        notesDraft.value = '';
        await load();
        if (updated.status === 'released') emit('released');
    } finally {
        actingStep.value = null;
    }
}

async function submitCancel(): Promise<void> {
    if (!activeReview.value) return;
    cancelling.value = true;

    try {
        await store.cancel(activeReview.value.id, cancelReason.value || null);
        showCancelForm.value = false;
        cancelReason.value = '';
        await load();
    } finally {
        cancelling.value = false;
    }
}

onMounted(load);
</script>

<template>
    <div class="surface-card">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-700">Release</h2>
            <button
                v-if="!activeReview && prisonerStatus === 'in_custody' && auth.hasRole('officer', 'admin')"
                type="button"
                :disabled="scheduling"
                class="text-sm font-medium text-slate-900 hover:underline disabled:opacity-50"
                @click="schedule"
            >
                {{ scheduling ? 'Scheduling…' : '+ Schedule release' }}
            </button>
        </div>

        <div v-if="activeReview" class="mt-4 space-y-4">
            <p v-if="activeReview.has_open_court_cases" class="rounded-xl bg-amber-50 px-3.5 py-2.5 text-xs text-amber-800">
                This prisoner has an open court case on file.
            </p>
            <p v-if="activeReview.has_unreleased_property" class="rounded-xl bg-amber-50 px-3.5 py-2.5 text-xs text-amber-800">
                This prisoner still has unreleased property on file.
            </p>

            <ol class="space-y-3">
                <li v-for="step in RELEASE_STEPS" :key="step.value" class="rounded-xl border border-slate-100 bg-white p-3 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-slate-900">{{ step.label }}</span>
                        <span v-if="stepEntry(step.value)" class="text-xs font-medium text-emerald-700">✓ Complete</span>
                        <span v-else-if="activeReview.next_step === step.value" class="text-xs font-medium text-primary-600">Next</span>
                        <span v-else class="text-xs text-slate-400">Pending</span>
                    </div>

                    <p v-if="stepEntry(step.value)" class="mt-1 text-xs text-slate-500">
                        {{ stepEntry(step.value)?.completed_by }} · {{ formatDate(stepEntry(step.value)!.completed_at) }}
                        <span v-if="stepEntry(step.value)?.notes"> · {{ stepEntry(step.value)?.notes }}</span>
                    </p>

                    <div v-else-if="activeReview.next_step === step.value">
                        <div v-if="canActOnStep(step.gate)" class="mt-2 flex items-end gap-2">
                            <input
                                v-model="notesDraft"
                                type="text"
                                placeholder="Notes (optional)"
                                class="field-input-sm flex-1 text-xs"
                            />
                            <button
                                type="button"
                                :disabled="actingStep === step.value"
                                class="btn-primary-sm"
                                @click="submitStep(step.value, step.endpoint)"
                            >
                                {{ actingStep === step.value ? 'Saving…' : step.gate === 'supervisor' ? 'Approve' : 'Complete' }}
                            </button>
                        </div>
                        <p v-else class="mt-1 text-xs text-slate-400">
                            Awaiting {{ step.gate === 'supervisor' ? 'a supervisor' : 'an officer' }} to complete this step.
                        </p>
                    </div>
                </li>
            </ol>

            <div v-if="auth.hasRole('admin')" class="border-t border-slate-100 pt-3">
                <button type="button" class="text-xs font-medium text-red-600 hover:underline" @click="showCancelForm = !showCancelForm">
                    {{ showCancelForm ? 'Cancel' : 'Cancel release review' }}
                </button>
                <form v-if="showCancelForm" class="mt-2 flex items-end gap-2 rounded-xl border border-slate-100 bg-slate-50/60 p-3" @submit.prevent="submitCancel">
                    <div class="flex-1">
                        <label class="field-label">Reason (optional)</label>
                        <input v-model="cancelReason" type="text" class="mt-1 field-input-sm text-xs" />
                    </div>
                    <button
                        type="submit"
                        :disabled="cancelling"
                        class="rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-red-700 disabled:opacity-50"
                    >
                        {{ cancelling ? 'Cancelling…' : 'Confirm cancel' }}
                    </button>
                </form>
            </div>
        </div>

        <div v-else-if="latestReview" class="mt-4 rounded-xl border border-slate-100 bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm text-slate-700">
                    {{ latestReview.status === 'released' ? `Released ${formatDate(latestReview.released_at!)}` : 'Release review cancelled' }}
                </p>
                <StatusBadge :status="latestReview.status" />
            </div>
            <p v-if="latestReview.status === 'cancelled' && latestReview.cancellation_reason" class="mt-1 text-xs text-slate-500">
                Reason: {{ latestReview.cancellation_reason }}
            </p>
        </div>

        <p v-else-if="!store.loading" class="mt-4 text-sm text-slate-500">No release review on file.</p>
    </div>
</template>

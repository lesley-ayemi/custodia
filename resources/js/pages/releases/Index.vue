<script setup lang="ts">
import { onMounted, ref } from 'vue';
import DashboardLayout from '../../layouts/DashboardLayout.vue';
import StatusBadge from '../../components/StatusBadge.vue';
import { useReleaseStore } from '../../stores/release';
import { useAuthStore } from '../../stores/auth';
import { RELEASE_STEPS } from '../../types/release';
import type { ReleaseReview, ReleaseReviewStatus, ReleaseStepName } from '../../types/release';

const store = useReleaseStore();
const auth = useAuthStore();

const statusFilter = ref<ReleaseReviewStatus | ''>('in_progress');
const actingKey = ref<string | null>(null);
const notesDraft = ref('');

const tabs: { label: string; value: ReleaseReviewStatus | '' }[] = [
    { label: 'In progress', value: 'in_progress' },
    { label: 'Released', value: 'released' },
    { label: 'Cancelled', value: 'cancelled' },
    { label: 'All', value: '' },
];

function load(): void {
    store.fetchAll(statusFilter.value);
}

function setFilter(value: ReleaseReviewStatus | ''): void {
    statusFilter.value = value;
    load();
}

function formatDate(value: string): string {
    return new Date(value).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
}

function stepEntry(review: ReleaseReview, stepValue: ReleaseStepName) {
    return review.steps.find((s) => s.step === stepValue) ?? null;
}

function canActOnStep(gate: 'operational' | 'supervisor'): boolean {
    return gate === 'operational' ? auth.hasRole('officer', 'admin') : auth.hasRole('supervisor', 'admin');
}

async function submitStep(review: ReleaseReview, step: ReleaseStepName, endpoint: string): Promise<void> {
    actingKey.value = `${review.id}:${step}`;

    try {
        await store.completeStep(review.id, endpoint, notesDraft.value || null);
        notesDraft.value = '';
        load();
    } finally {
        actingKey.value = null;
    }
}

onMounted(load);
</script>

<template>
    <DashboardLayout>
        <h1 class="text-2xl font-bold text-slate-900">Releases</h1>

        <div class="mt-4 flex gap-2">
            <button
                v-for="tab in tabs"
                :key="tab.label"
                type="button"
                :class="statusFilter === tab.value ? 'tab-pill-active' : 'tab-pill-inactive'"
                @click="setFilter(tab.value)"
            >
                {{ tab.label }}
            </button>
        </div>

        <div class="mt-6 space-y-4">
            <div v-for="review in store.reviews" :key="review.id" class="rounded-xl border border-slate-100 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-900">{{ review.prisoner_name }}</p>
                    <StatusBadge :status="review.status" />
                </div>
                <p class="mt-1 text-xs text-slate-400">Scheduled {{ formatDate(review.initiated_at) }} by {{ review.initiated_by }}</p>

                <template v-if="review.status === 'in_progress'">
                    <p v-if="review.has_open_court_cases" class="mt-2 rounded-xl bg-amber-50 px-3.5 py-2.5 text-xs text-amber-800">
                        Open court case on file.
                    </p>
                    <p v-if="review.has_unreleased_property" class="mt-2 rounded-xl bg-amber-50 px-3.5 py-2.5 text-xs text-amber-800">
                        Unreleased property on file.
                    </p>

                    <div class="mt-3 flex flex-wrap gap-2">
                        <span
                            v-for="step in RELEASE_STEPS"
                            :key="step.value"
                            class="rounded-full px-2.5 py-1 text-xs"
                            :class="stepEntry(review, step.value) ? 'bg-emerald-100 text-emerald-800' : review.next_step === step.value ? 'bg-primary-100 text-primary-700' : 'bg-slate-100 text-slate-500'"
                        >
                            {{ stepEntry(review, step.value) ? '✓ ' : '' }}{{ step.label }}
                        </span>
                    </div>

                    <div
                        v-for="step in RELEASE_STEPS.filter((s) => s.value === review.next_step)"
                        :key="step.value"
                        class="mt-3 border-t border-slate-100 pt-3"
                    >
                        <div v-if="canActOnStep(step.gate)" class="flex items-end gap-2">
                            <input
                                v-model="notesDraft"
                                type="text"
                                placeholder="Notes (optional)"
                                class="field-input-sm flex-1 text-xs"
                            />
                            <button
                                type="button"
                                :disabled="actingKey === `${review.id}:${step.value}`"
                                class="btn-primary-sm"
                                @click="submitStep(review, step.value, step.endpoint)"
                            >
                                {{ actingKey === `${review.id}:${step.value}` ? 'Saving…' : step.gate === 'supervisor' ? 'Approve' : 'Complete' }}
                                {{ step.label }}
                            </button>
                        </div>
                        <p v-else class="text-xs text-slate-400">
                            Awaiting {{ step.gate === 'supervisor' ? 'a supervisor' : 'an officer' }} for {{ step.label.toLowerCase() }}.
                        </p>
                    </div>
                </template>
                <p v-else-if="review.status === 'cancelled' && review.cancellation_reason" class="mt-2 text-xs text-slate-500">
                    Reason: {{ review.cancellation_reason }}
                </p>
            </div>

            <p v-if="!store.loading && store.reviews.length === 0" class="text-sm text-slate-500">No release reviews found.</p>
        </div>
    </DashboardLayout>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import DashboardLayout from '../../layouts/DashboardLayout.vue';
import StatusBadge from '../../components/StatusBadge.vue';
import { useAdmissionStore } from '../../stores/admission';
import { useAuthStore } from '../../stores/auth';

const route = useRoute();
const store = useAdmissionStore();
const auth = useAuthStore();

const busyKey = ref<string | null>(null);
const error = ref<string | null>(null);

const legalAuthorityRef = ref('');
const assessmentNotes = ref('');
const classification = ref('low');

const canOperate = computed(() => auth.hasRole('officer', 'admin'));

function formatDate(value: string): string {
    return new Date(value).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
}

async function load(): Promise<void> {
    await store.fetchOne(Number(route.params.id));
}

function extractError(err: unknown): string {
    const response = (err as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } }).response;
    const errors = response?.data?.errors;
    if (errors) return Object.values(errors).flat().join(' ');
    return response?.data?.message ?? 'Something went wrong.';
}

async function run(key: string, action: () => Promise<unknown>): Promise<void> {
    error.value = null;
    busyKey.value = key;

    try {
        await action();
        await load();
    } catch (err) {
        error.value = extractError(err);
    } finally {
        busyKey.value = null;
    }
}

const submitLegalAuthority = () =>
    run('legal', () => store.recordLegalAuthority(store.current!.id, legalAuthorityRef.value));

const submitAssessment = () =>
    run('assessment', () => store.recordAssessment(store.current!.id, assessmentNotes.value));

const submitClassification = () =>
    run('classification', () => store.recordClassification(store.current!.id, classification.value));

const submitAdvance = () => run('advance', () => store.advanceToMedical(store.current!.id));
const submitCompleteMedical = () => run('medical', () => store.completeMedical(store.current!.id));
const submitCompleteHousing = () => run('housing', () => store.completeHousing(store.current!.id));

onMounted(load);
</script>

<template>
    <DashboardLayout>
        <div v-if="store.current">
            <div class="flex items-start justify-between">
                <div>
                    <router-link
                        :to="{ name: 'prisoners.show', params: { id: store.current.prisoner_id } }"
                        class="text-sm font-medium text-slate-500 hover:underline"
                    >
                        {{ store.current.prisoner_name }}
                    </router-link>
                    <h1 class="text-xl font-semibold text-slate-900">Admission</h1>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ store.current.admission_reason }} · Admitted {{ formatDate(store.current.admission_date) }}
                    </p>
                </div>
                <StatusBadge :status="store.current.status" />
            </div>

            <p v-if="error" class="mt-3 text-sm text-red-600">{{ error }}</p>

            <div class="mt-6 space-y-4">
                <div class="rounded-lg border border-slate-200 bg-white p-6">
                    <h2 class="text-sm font-semibold text-slate-700">1. Legal authority</h2>
                    <p v-if="store.current.legal_authority_reference" class="mt-2 text-sm text-slate-600">
                        ✓ {{ store.current.legal_authority_reference }}
                    </p>
                    <form v-else-if="canOperate" class="mt-3 flex items-end gap-2" @submit.prevent="submitLegalAuthority">
                        <input
                            v-model="legalAuthorityRef"
                            type="text"
                            required
                            placeholder="Remand Order #RO-2026-0045"
                            class="block flex-1 rounded-md border border-slate-300 px-3 py-1.5 text-sm"
                        />
                        <button
                            type="submit"
                            :disabled="busyKey === 'legal'"
                            class="rounded-md bg-slate-900 px-3 py-1.5 text-sm font-medium text-white disabled:opacity-50"
                        >
                            {{ busyKey === 'legal' ? 'Saving…' : 'Record' }}
                        </button>
                    </form>
                </div>

                <template v-if="store.current.status !== 'draft'">
                    <div class="rounded-lg border border-slate-200 bg-white p-6">
                        <h2 class="text-sm font-semibold text-slate-700">2. Belongings</h2>
                        <p class="mt-2 text-sm text-slate-600">
                            {{ store.current.has_property ? '✓ Recorded' : 'Not yet recorded' }} — use the Property panel on the
                            <router-link :to="{ name: 'prisoners.show', params: { id: store.current.prisoner_id } }" class="underline">
                                prisoner's profile
                            </router-link>
                            (optional if they arrived with nothing).
                        </p>
                    </div>

                    <div class="rounded-lg border border-slate-200 bg-white p-6">
                        <h2 class="text-sm font-semibold text-slate-700">3. Initial assessment</h2>
                        <p v-if="store.current.initial_assessment_notes" class="mt-2 text-sm text-slate-600">
                            ✓ {{ store.current.initial_assessment_notes }}
                        </p>
                        <form
                            v-else-if="canOperate && store.current.status === 'processing'"
                            class="mt-3 flex items-end gap-2"
                            @submit.prevent="submitAssessment"
                        >
                            <input
                                v-model="assessmentNotes"
                                type="text"
                                required
                                placeholder="No immediate concerns noted."
                                class="block flex-1 rounded-md border border-slate-300 px-3 py-1.5 text-sm"
                            />
                            <button
                                type="submit"
                                :disabled="busyKey === 'assessment'"
                                class="rounded-md bg-slate-900 px-3 py-1.5 text-sm font-medium text-white disabled:opacity-50"
                            >
                                {{ busyKey === 'assessment' ? 'Saving…' : 'Record' }}
                            </button>
                        </form>
                    </div>

                    <div class="rounded-lg border border-slate-200 bg-white p-6">
                        <h2 class="text-sm font-semibold text-slate-700">4. Security classification</h2>
                        <p v-if="store.current.security_classification" class="mt-2 text-sm text-slate-600 capitalize">
                            ✓ {{ store.current.security_classification }}
                        </p>
                        <form
                            v-else-if="canOperate && store.current.status === 'processing'"
                            class="mt-3 flex items-end gap-2"
                            @submit.prevent="submitClassification"
                        >
                            <select v-model="classification" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="maximum">Maximum</option>
                            </select>
                            <button
                                type="submit"
                                :disabled="busyKey === 'classification'"
                                class="rounded-md bg-slate-900 px-3 py-1.5 text-sm font-medium text-white disabled:opacity-50"
                            >
                                {{ busyKey === 'classification' ? 'Saving…' : 'Record' }}
                            </button>
                        </form>
                    </div>

                    <div v-if="store.current.status === 'processing' && canOperate" class="flex justify-end">
                        <button
                            type="button"
                            :disabled="!store.current.security_classification || busyKey === 'advance'"
                            class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
                            @click="submitAdvance"
                        >
                            {{ busyKey === 'advance' ? 'Sending…' : 'Send for medical screening' }}
                        </button>
                    </div>

                    <div v-if="store.current.status === 'awaiting_medical'" class="rounded-lg border border-slate-200 bg-white p-6">
                        <h2 class="text-sm font-semibold text-slate-700">5. Medical screening</h2>
                        <p class="mt-2 text-sm text-slate-500">Awaiting medical staff to complete a screening.</p>
                        <button
                            v-if="auth.hasRole('medical', 'admin')"
                            type="button"
                            :disabled="busyKey === 'medical'"
                            class="mt-3 rounded-md bg-slate-900 px-3 py-1.5 text-sm font-medium text-white disabled:opacity-50"
                            @click="submitCompleteMedical"
                        >
                            {{ busyKey === 'medical' ? 'Saving…' : 'Complete medical screening' }}
                        </button>
                    </div>

                    <div v-if="store.current.status === 'awaiting_housing'" class="rounded-lg border border-slate-200 bg-white p-6">
                        <h2 class="text-sm font-semibold text-slate-700">6. Housing assignment</h2>
                        <p v-if="store.current.has_housing" class="mt-2 text-sm text-slate-600">✓ Cell assigned</p>
                        <p v-else class="mt-2 text-sm text-slate-600">
                            Not yet assigned — assign a cell from the
                            <router-link :to="{ name: 'prisoners.show', params: { id: store.current.prisoner_id } }" class="underline">
                                prisoner's profile
                            </router-link>
                            first.
                        </p>
                        <button
                            v-if="canOperate"
                            type="button"
                            :disabled="!store.current.has_housing || busyKey === 'housing'"
                            class="mt-3 rounded-md bg-slate-900 px-3 py-1.5 text-sm font-medium text-white disabled:opacity-50"
                            @click="submitCompleteHousing"
                        >
                            {{ busyKey === 'housing' ? 'Saving…' : 'Complete admission' }}
                        </button>
                    </div>

                    <div v-if="store.current.status === 'completed'" class="rounded-lg border border-emerald-200 bg-emerald-50 p-6">
                        <p class="text-sm font-medium text-emerald-800">Admission completed {{ formatDate(store.current.completed_at!) }}.</p>
                    </div>
                </template>
            </div>
        </div>
    </DashboardLayout>
</template>

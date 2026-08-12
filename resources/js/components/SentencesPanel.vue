<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import StatusBadge from './StatusBadge.vue';
import { useSentenceStore } from '../stores/sentence';
import { useAuthStore } from '../stores/auth';
import type { SentenceFormData } from '../types/sentence';

const props = defineProps<{ prisonerId: number }>();

const store = useSentenceStore();
const auth = useAuthStore();

const showForm = ref(false);
const submitting = ref(false);

const form = reactive<SentenceFormData>({
    case_number: '',
    court: '',
    offence: '',
    sentence_start: new Date().toISOString().slice(0, 10),
    sentence_end: null,
    sentence_type: 'custodial',
    parole_eligibility_date: null,
    legal_status: 'convicted',
});

function formatDate(value: string): string {
    return new Date(value).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
}

async function load(): Promise<void> {
    await store.fetchForPrisoner(props.prisonerId);
}

async function submit(): Promise<void> {
    submitting.value = true;

    try {
        await store.record(props.prisonerId, form);
        showForm.value = false;
        form.case_number = '';
        form.court = '';
        form.offence = '';
        form.sentence_end = null;
        form.parole_eligibility_date = null;
        await load();
    } finally {
        submitting.value = false;
    }
}

onMounted(load);
</script>

<template>
    <div class="surface-card">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-700">Sentences</h2>
            <button
                v-if="auth.hasRole('officer', 'admin')"
                type="button"
                class="text-sm font-medium text-primary-600 hover:underline"
                @click="showForm = !showForm"
            >
                {{ showForm ? 'Cancel' : '+ Record sentence' }}
            </button>
        </div>

        <form v-if="showForm" class="mt-4 space-y-3 rounded-xl border border-slate-100 bg-slate-50/60 p-4" @submit.prevent="submit">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="field-label">Case number</label>
                    <input
                        v-model="form.case_number"
                        type="text"
                        required
                        class="mt-1 field-input-sm"
                    />
                </div>
                <div>
                    <label class="field-label">Court</label>
                    <input
                        v-model="form.court"
                        type="text"
                        required
                        class="mt-1 field-input-sm"
                    />
                </div>
            </div>
            <div>
                <label class="field-label">Offence</label>
                <input
                    v-model="form.offence"
                    type="text"
                    required
                    class="mt-1 field-input-sm"
                />
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="field-label">Sentence start</label>
                    <input
                        v-model="form.sentence_start"
                        type="date"
                        required
                        class="mt-1 field-input-sm"
                    />
                </div>
                <div>
                    <label class="field-label">Sentence end (optional)</label>
                    <input
                        v-model="form.sentence_end"
                        type="date"
                        class="mt-1 field-input-sm"
                    />
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="field-label">Sentence type</label>
                    <select v-model="form.sentence_type" class="mt-1 field-input-sm">
                        <option value="custodial">Custodial</option>
                        <option value="suspended">Suspended</option>
                        <option value="life">Life</option>
                    </select>
                </div>
                <div>
                    <label class="field-label">Legal status</label>
                    <select v-model="form.legal_status" class="mt-1 field-input-sm">
                        <option value="convicted">Convicted</option>
                        <option value="on_appeal">On appeal</option>
                        <option value="discharged">Discharged</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="field-label">Parole eligibility date (optional)</label>
                <input
                    v-model="form.parole_eligibility_date"
                    type="date"
                    class="mt-1 field-input-sm"
                />
            </div>
            <button
                type="submit"
                :disabled="submitting"
                class="btn-primary-sm"
            >
                {{ submitting ? 'Recording…' : 'Record sentence' }}
            </button>
        </form>

        <div class="mt-4 space-y-3">
            <div v-for="sentence in store.sentences" :key="sentence.id" class="rounded-xl border border-slate-100 bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-900">{{ sentence.case_number }}</p>
                        <p class="text-sm text-slate-600">{{ sentence.court }} · {{ sentence.offence }}</p>
                        <p class="mt-1 text-xs text-slate-400">
                            {{ formatDate(sentence.sentence_start) }} — {{ sentence.sentence_end ? formatDate(sentence.sentence_end) : 'Life' }}
                        </p>
                        <p v-if="sentence.parole_eligibility_date" class="mt-1 text-xs text-slate-400">
                            Parole eligible {{ formatDate(sentence.parole_eligibility_date) }}
                        </p>
                    </div>
                    <div class="flex flex-col items-end gap-1">
                        <StatusBadge :status="sentence.sentence_type" />
                        <StatusBadge :status="sentence.legal_status" />
                    </div>
                </div>
            </div>

            <p v-if="!store.loading && store.sentences.length === 0" class="text-sm text-slate-500">No sentences on file.</p>
        </div>
    </div>
</template>

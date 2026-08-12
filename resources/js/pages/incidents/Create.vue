<script setup lang="ts">
import { reactive, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import DashboardLayout from '../../layouts/DashboardLayout.vue';
import { useIncidentStore } from '../../stores/incident';
import { usePrisonerStore } from '../../stores/prisoner';
import type { IncidentFormData } from '../../types/incident';
import type { Prisoner } from '../../types/prisoner';

const store = useIncidentStore();
const prisonerStore = usePrisonerStore();
const router = useRouter();

const form = reactive<IncidentFormData>({
    prisoner_id: null,
    type: 'rule_violation',
    severity: 'low',
    location: '',
    description: '',
    occurred_at: new Date().toISOString().slice(0, 16),
});

const prisonerSearch = ref('');
const selectedPrisoner = ref<Prisoner | null>(null);
const submitting = ref(false);
const error = ref<string | null>(null);
let searchTimeout: ReturnType<typeof setTimeout> | undefined;

watch(prisonerSearch, (value) => {
    clearTimeout(searchTimeout);
    if (!value) return;
    searchTimeout = setTimeout(() => prisonerStore.fetchList(value, 1), 300);
});

function selectPrisoner(prisoner: Prisoner): void {
    selectedPrisoner.value = prisoner;
    form.prisoner_id = prisoner.id;
    prisonerSearch.value = '';
    prisonerStore.prisoners = [];
}

async function submit(): Promise<void> {
    submitting.value = true;
    error.value = null;

    try {
        await store.create(form);
        await router.push({ name: 'incidents.index' });
    } catch {
        error.value = 'Please check the form for errors.';
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <DashboardLayout>
        <h1 class="text-2xl font-bold text-slate-900">Report incident</h1>

        <form class="mt-6 max-w-lg space-y-4" @submit.prevent="submit">
            <div>
                <label class="field-label">Prisoner</label>
                <div v-if="selectedPrisoner" class="mt-1 flex items-center justify-between rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm">
                    <span>{{ selectedPrisoner.full_name }} ({{ selectedPrisoner.prisoner_number }})</span>
                    <button type="button" class="text-slate-400 hover:text-slate-700" @click="selectedPrisoner = null; form.prisoner_id = null">
                        Change
                    </button>
                </div>
                <template v-else>
                    <input
                        v-model="prisonerSearch"
                        type="text"
                        placeholder="Search prisoner by name or number…"
                        class="mt-1 field-input"
                    />
                    <ul v-if="prisonerStore.prisoners.length > 0" class="mt-1 max-h-40 overflow-y-auto rounded-xl border border-slate-200 text-sm shadow-sm">
                        <li
                            v-for="prisoner in prisonerStore.prisoners"
                            :key="prisoner.id"
                            class="cursor-pointer px-3 py-2 hover:bg-slate-50"
                            @click="selectPrisoner(prisoner)"
                        >
                            {{ prisoner.full_name }} ({{ prisoner.prisoner_number }})
                        </li>
                    </ul>
                </template>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="field-label">Type</label>
                    <select v-model="form.type" class="mt-1 field-input">
                        <option value="property_damage">Property Damage</option>
                        <option value="rule_violation">Rule Violation</option>
                        <option value="accident">Accident</option>
                        <option value="altercation">Altercation</option>
                        <option value="contraband_found">Contraband Found</option>
                        <option value="medical_emergency">Medical Emergency</option>
                    </select>
                </div>
                <div>
                    <label class="field-label">Severity</label>
                    <select v-model="form.severity" class="mt-1 field-input">
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="field-label">Location</label>
                <input v-model="form.location" type="text" required class="mt-1 field-input" />
            </div>

            <div>
                <label class="field-label">Occurred at</label>
                <input
                    v-model="form.occurred_at"
                    type="datetime-local"
                    required
                    class="mt-1 field-input"
                />
            </div>

            <div>
                <label class="field-label">Description</label>
                <textarea
                    v-model="form.description"
                    rows="4"
                    required
                    class="mt-1 field-input"
                />
            </div>

            <p v-if="error" class="rounded-xl bg-red-50 px-3.5 py-2.5 text-sm text-red-700">{{ error }}</p>

            <div class="flex gap-3">
                <button
                    type="submit"
                    :disabled="submitting || !form.prisoner_id"
                    class="btn-primary disabled:opacity-50"
                >
                    {{ submitting ? 'Saving…' : 'Report incident' }}
                </button>
                <router-link :to="{ name: 'incidents.index' }" class="rounded-xl px-4 py-2.5 text-sm font-medium text-slate-600 hover:text-slate-900">
                    Cancel
                </router-link>
            </div>
        </form>
    </DashboardLayout>
</template>

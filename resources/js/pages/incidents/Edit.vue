<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import DashboardLayout from '../../layouts/DashboardLayout.vue';
import { useIncidentStore } from '../../stores/incident';
import type { Incident, IncidentUpdateFormData } from '../../types/incident';

const route = useRoute();
const router = useRouter();
const store = useIncidentStore();

const incident = ref<Incident | null>(null);
const form = reactive<IncidentUpdateFormData>({
    type: 'rule_violation',
    severity: 'low',
    location: '',
    description: '',
    occurred_at: '',
    status: 'reported',
});

const saving = ref(false);
const deleting = ref(false);
const error = ref<string | null>(null);

async function load(): Promise<void> {
    incident.value = await store.fetchOne(Number(route.params.id));
    form.type = incident.value.type;
    form.severity = incident.value.severity;
    form.location = incident.value.location;
    form.description = incident.value.description;
    form.occurred_at = incident.value.occurred_at.slice(0, 16);
    form.status = incident.value.status;
}

async function save(): Promise<void> {
    if (!incident.value) return;
    saving.value = true;
    error.value = null;

    try {
        incident.value = await store.update(incident.value.id, form);
    } catch {
        error.value = 'Please check the form for errors.';
    } finally {
        saving.value = false;
    }
}

async function remove(): Promise<void> {
    if (!incident.value) return;
    deleting.value = true;

    try {
        await store.remove(incident.value.id);
        await router.push({ name: 'incidents.index' });
    } finally {
        deleting.value = false;
    }
}

onMounted(load);
</script>

<template>
    <DashboardLayout>
        <div v-if="incident">
            <h1 class="text-2xl font-bold text-slate-900">{{ incident.incident_number }}</h1>
            <p class="mt-1 text-sm text-slate-500">
                {{ incident.prisoner_name }} ({{ incident.prisoner_number }}) · reported by {{ incident.officer_name }}
            </p>

            <form class="mt-6 max-w-lg space-y-4 surface-card" @submit.prevent="save">
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
                    <label class="field-label">Status</label>
                    <select v-model="form.status" class="mt-1 field-input">
                        <option value="reported">Reported</option>
                        <option value="under_review">Under Review</option>
                        <option value="resolved">Resolved</option>
                    </select>
                </div>

                <div>
                    <label class="field-label">Location</label>
                    <input
                        v-model="form.location"
                        type="text"
                        required
                        class="mt-1 field-input"
                    />
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

                <button
                    type="submit"
                    :disabled="saving"
                    class="btn-primary disabled:opacity-50"
                >
                    {{ saving ? 'Saving…' : 'Save changes' }}
                </button>
            </form>

            <div class="mt-6">
                <button
                    type="button"
                    :disabled="deleting"
                    class="btn-danger"
                    @click="remove"
                >
                    {{ deleting ? 'Deleting…' : 'Delete incident' }}
                </button>
            </div>
        </div>
    </DashboardLayout>
</template>

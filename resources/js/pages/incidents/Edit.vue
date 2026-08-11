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
            <h1 class="text-xl font-semibold text-slate-900">{{ incident.incident_number }}</h1>
            <p class="mt-1 text-sm text-slate-500">
                {{ incident.prisoner_name }} ({{ incident.prisoner_number }}) · reported by {{ incident.officer_name }}
            </p>

            <form class="mt-6 max-w-lg space-y-4 rounded-lg border border-slate-200 bg-white p-6" @submit.prevent="save">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Type</label>
                        <select v-model="form.type" class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                            <option value="property_damage">Property Damage</option>
                            <option value="rule_violation">Rule Violation</option>
                            <option value="accident">Accident</option>
                            <option value="altercation">Altercation</option>
                            <option value="contraband_found">Contraband Found</option>
                            <option value="medical_emergency">Medical Emergency</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Severity</label>
                        <select v-model="form.severity" class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Status</label>
                    <select v-model="form.status" class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                        <option value="reported">Reported</option>
                        <option value="under_review">Under Review</option>
                        <option value="resolved">Resolved</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Location</label>
                    <input
                        v-model="form.location"
                        type="text"
                        required
                        class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Occurred at</label>
                    <input
                        v-model="form.occurred_at"
                        type="datetime-local"
                        required
                        class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Description</label>
                    <textarea
                        v-model="form.description"
                        rows="4"
                        required
                        class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                    />
                </div>

                <p v-if="error" class="text-sm text-red-600">{{ error }}</p>

                <button
                    type="submit"
                    :disabled="saving"
                    class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 disabled:opacity-50"
                >
                    {{ saving ? 'Saving…' : 'Save changes' }}
                </button>
            </form>

            <div class="mt-6">
                <button
                    type="button"
                    :disabled="deleting"
                    class="rounded-md border border-red-200 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50 disabled:opacity-50"
                    @click="remove"
                >
                    {{ deleting ? 'Deleting…' : 'Delete incident' }}
                </button>
            </div>
        </div>
    </DashboardLayout>
</template>

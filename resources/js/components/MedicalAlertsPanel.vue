<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import StatusBadge from './StatusBadge.vue';
import { useMedicalStore } from '../stores/medical';
import { useAuthStore } from '../stores/auth';
import type { MedicalAlertFormData, MedicalAlertSeverity } from '../types/medical';

const props = defineProps<{ prisonerId: number }>();

const store = useMedicalStore();
const auth = useAuthStore();

const showForm = ref(false);
const submitting = ref(false);
const form = reactive<MedicalAlertFormData>({ message: '', severity: 'medium' });

const editingId = ref<number | null>(null);
const editForm = reactive<MedicalAlertFormData>({ message: '', severity: 'medium' });
const resolvingId = ref<number | null>(null);

function formatDate(value: string): string {
    return new Date(value).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
}

async function load(): Promise<void> {
    await store.fetchAlerts(props.prisonerId);
}

async function submit(): Promise<void> {
    submitting.value = true;

    try {
        await store.addAlert(props.prisonerId, form);
        form.message = '';
        form.severity = 'medium';
        showForm.value = false;
        await load();
    } finally {
        submitting.value = false;
    }
}

function startEdit(id: number, message: string, severity: MedicalAlertSeverity): void {
    editingId.value = id;
    editForm.message = message;
    editForm.severity = severity;
}

async function saveEdit(id: number): Promise<void> {
    submitting.value = true;

    try {
        await store.updateAlert(id, editForm);
        editingId.value = null;
        await load();
    } finally {
        submitting.value = false;
    }
}

async function resolve(id: number): Promise<void> {
    resolvingId.value = id;

    try {
        await store.resolveAlert(id);
        await load();
    } finally {
        resolvingId.value = null;
    }
}

onMounted(load);
</script>

<template>
    <div class="rounded-lg border border-slate-200 bg-white p-6">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-700">Medical alerts</h2>
            <button
                v-if="auth.hasRole('medical', 'admin')"
                type="button"
                class="text-sm font-medium text-slate-900 hover:underline"
                @click="showForm = !showForm"
            >
                {{ showForm ? 'Cancel' : '+ Add alert' }}
            </button>
        </div>

        <form v-if="showForm" class="mt-4 flex items-end gap-2 rounded-md border border-slate-200 bg-slate-50 p-4" @submit.prevent="submit">
            <div class="flex-1">
                <label class="block text-xs font-medium text-slate-600">Message</label>
                <input
                    v-model="form.message"
                    type="text"
                    required
                    placeholder="Requires medication at 14:00"
                    class="mt-1 block w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm"
                />
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600">Severity</label>
                <select v-model="form.severity" class="mt-1 block rounded-md border border-slate-300 px-2 py-1.5 text-sm">
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                </select>
            </div>
            <button
                type="submit"
                :disabled="submitting"
                class="shrink-0 rounded-md bg-slate-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-slate-800 disabled:opacity-50"
            >
                {{ submitting ? 'Saving…' : 'Add' }}
            </button>
        </form>

        <ul class="mt-4 space-y-2">
            <li v-for="alert in store.alerts" :key="alert.id" class="rounded-md border border-slate-200 p-3">
                <template v-if="editingId === alert.id">
                    <div class="flex items-end gap-2">
                        <input v-model="editForm.message" type="text" class="flex-1 rounded-md border border-slate-300 px-2 py-1 text-sm" />
                        <select v-model="editForm.severity" class="rounded-md border border-slate-300 px-2 py-1 text-sm">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                        </select>
                        <button type="button" :disabled="submitting" class="text-xs font-medium text-emerald-700 hover:underline" @click="saveEdit(alert.id)">
                            Save
                        </button>
                        <button type="button" class="text-xs text-slate-500 hover:underline" @click="editingId = null">Cancel</button>
                    </div>
                </template>
                <template v-else>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-slate-900">{{ alert.message }}</span>
                        <StatusBadge :status="alert.severity" />
                    </div>
                    <div class="mt-1 flex items-center justify-between">
                        <p class="text-xs text-slate-400">{{ alert.created_by }} · {{ formatDate(alert.created_at) }}</p>
                        <div v-if="auth.hasRole('medical', 'admin')" class="flex gap-3 text-xs">
                            <button type="button" class="text-slate-500 hover:underline" @click="startEdit(alert.id, alert.message, alert.severity)">
                                Edit
                            </button>
                            <button
                                type="button"
                                :disabled="resolvingId === alert.id"
                                class="text-emerald-700 hover:underline disabled:opacity-50"
                                @click="resolve(alert.id)"
                            >
                                Resolve
                            </button>
                        </div>
                    </div>
                </template>
            </li>
            <p v-if="!store.loading && store.alerts.length === 0" class="text-sm text-slate-500">No active medical alerts.</p>
        </ul>
    </div>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import DashboardLayout from '../../layouts/DashboardLayout.vue';
import StatusBadge from '../../components/StatusBadge.vue';
import { useProgrammeStore } from '../../stores/programme';
import { useAuthStore } from '../../stores/auth';
import type { ProgrammeCategory, ProgrammeStatus } from '../../types/programme';

const store = useProgrammeStore();
const auth = useAuthStore();

const error = ref<string | null>(null);
const busy = ref(false);

const categories: { value: ProgrammeCategory; label: string }[] = [
    { value: 'education', label: 'Education' },
    { value: 'counselling', label: 'Counselling' },
    { value: 'vocational_training', label: 'Vocational training' },
    { value: 'substance_misuse', label: 'Substance misuse' },
    { value: 'employment_training', label: 'Employment training' },
    { value: 'life_skills', label: 'Life skills' },
    { value: 'other', label: 'Other' },
];

function categoryLabel(value: ProgrammeCategory): string {
    return categories.find((c) => c.value === value)?.label ?? value;
}

const showForm = ref(false);
const newProgramme = reactive({ name: '', category: 'education' as ProgrammeCategory, description: '', capacity: null as number | null });

const editingId = ref<number | null>(null);
const editProgramme = reactive({ name: '', category: 'education' as ProgrammeCategory, description: '', capacity: null as number | null, status: 'active' as ProgrammeStatus });

function extractError(err: unknown): string {
    const response = (err as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } }).response;
    const errors = response?.data?.errors;
    if (errors) return Object.values(errors).flat().join(' ');
    return response?.data?.message ?? 'Something went wrong.';
}

async function load(): Promise<void> {
    await store.fetchProgrammes();
}

async function submitNewProgramme(): Promise<void> {
    error.value = null;
    busy.value = true;

    try {
        await store.createProgramme({
            name: newProgramme.name,
            category: newProgramme.category,
            description: newProgramme.description,
            capacity: newProgramme.capacity,
        });
        newProgramme.name = '';
        newProgramme.category = 'education';
        newProgramme.description = '';
        newProgramme.capacity = null;
        showForm.value = false;
        await load();
    } catch (err) {
        error.value = extractError(err);
    } finally {
        busy.value = false;
    }
}

function startEdit(id: number): void {
    const programme = store.programmes.find((p) => p.id === id);
    if (!programme) return;

    editingId.value = id;
    editProgramme.name = programme.name;
    editProgramme.category = programme.category;
    editProgramme.description = programme.description ?? '';
    editProgramme.capacity = programme.capacity;
    editProgramme.status = programme.status;
}

async function saveEdit(id: number): Promise<void> {
    error.value = null;
    busy.value = true;

    try {
        await store.updateProgramme(id, { ...editProgramme });
        editingId.value = null;
        await load();
    } catch (err) {
        error.value = extractError(err);
    } finally {
        busy.value = false;
    }
}

async function removeProgramme(id: number): Promise<void> {
    error.value = null;
    busy.value = true;

    try {
        await store.deleteProgramme(id);
        await load();
    } catch (err) {
        error.value = extractError(err);
    } finally {
        busy.value = false;
    }
}

onMounted(load);
</script>

<template>
    <DashboardLayout>
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-slate-900">Programmes</h1>
            <button
                v-if="auth.hasRole('admin')"
                type="button"
                class="btn-primary"
                @click="showForm = !showForm"
            >
                {{ showForm ? 'Cancel' : '+ Add programme' }}
            </button>
        </div>

        <p v-if="error" class="mt-3 rounded-xl bg-red-50 px-3.5 py-2.5 text-sm text-red-700">{{ error }}</p>

        <form v-if="showForm" class="mt-4 space-y-3 rounded-xl border border-slate-100 bg-slate-50/60 p-4" @submit.prevent="submitNewProgramme">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="field-label">Name</label>
                    <input v-model="newProgramme.name" type="text" required placeholder="Life Skills" class="mt-1 field-input-sm" />
                </div>
                <div>
                    <label class="field-label">Category</label>
                    <select v-model="newProgramme.category" class="mt-1 field-input-sm">
                        <option v-for="c in categories" :key="c.value" :value="c.value">{{ c.label }}</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="field-label">Description</label>
                <textarea v-model="newProgramme.description" rows="2" class="mt-1 field-input-sm"></textarea>
            </div>
            <div class="w-32">
                <label class="field-label">Capacity</label>
                <input v-model.number="newProgramme.capacity" type="number" min="1" class="mt-1 field-input-sm" />
            </div>
            <button type="submit" :disabled="busy" class="btn-primary-sm">
                Create
            </button>
        </form>

        <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div v-for="programme in store.programmes" :key="programme.id" class="rounded-xl border border-slate-100 bg-white p-4 shadow-sm">
                <template v-if="editingId === programme.id">
                    <div class="space-y-2">
                        <input v-model="editProgramme.name" type="text" class="field-input-sm w-full" />
                        <select v-model="editProgramme.category" class="field-input-sm w-full">
                            <option v-for="c in categories" :key="c.value" :value="c.value">{{ c.label }}</option>
                        </select>
                        <textarea v-model="editProgramme.description" rows="2" class="field-input-sm w-full"></textarea>
                        <div class="flex gap-2">
                            <input v-model.number="editProgramme.capacity" type="number" min="1" class="w-20 rounded-lg border border-slate-200 px-2 py-1 text-sm focus:border-primary-400 focus:outline-none" />
                            <select v-model="editProgramme.status" class="field-input-sm">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="flex gap-2 text-xs">
                            <button type="button" :disabled="busy" class="font-medium text-emerald-700 hover:underline" @click="saveEdit(programme.id)">
                                Save
                            </button>
                            <button type="button" class="text-slate-500 hover:underline" @click="editingId = null">Cancel</button>
                        </div>
                    </div>
                </template>
                <template v-else>
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="font-medium text-slate-900">{{ programme.name }}</p>
                            <p class="text-xs text-slate-500">{{ categoryLabel(programme.category) }}</p>
                        </div>
                        <StatusBadge :status="programme.status" />
                    </div>
                    <p v-if="programme.description" class="mt-2 text-xs text-slate-500">{{ programme.description }}</p>
                    <p class="mt-2 text-xs text-slate-400">
                        {{ programme.enrolled_count ?? 0 }} enrolled<span v-if="programme.capacity"> / {{ programme.capacity }} capacity</span>
                    </p>
                    <div v-if="auth.hasRole('admin')" class="mt-3 flex gap-3 text-xs">
                        <button type="button" class="text-slate-500 hover:underline" @click="startEdit(programme.id)">Edit</button>
                        <button type="button" class="text-red-600 hover:underline" @click="removeProgramme(programme.id)">Delete</button>
                    </div>
                </template>
            </div>

            <p v-if="!store.loading && store.programmes.length === 0" class="text-sm text-slate-500">No programmes on file.</p>
        </div>
    </DashboardLayout>
</template>

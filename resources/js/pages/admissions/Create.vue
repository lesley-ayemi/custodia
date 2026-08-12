<script setup lang="ts">
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import DashboardLayout from '../../layouts/DashboardLayout.vue';
import { useAdmissionStore } from '../../stores/admission';
import type { StartAdmissionFormData } from '../../types/admission';

const store = useAdmissionStore();
const router = useRouter();

const submitting = ref(false);
const error = ref<string | null>(null);

const form = reactive<StartAdmissionFormData>({
    first_name: '',
    last_name: '',
    date_of_birth: '',
    gender: 'male',
    expected_release_date: null,
    admission_date: new Date().toISOString().slice(0, 10),
    admission_reason: '',
});

function extractError(err: unknown): string {
    const response = (err as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } }).response;
    const errors = response?.data?.errors;
    if (errors) return Object.values(errors).flat().join(' ');
    return response?.data?.message ?? 'Something went wrong.';
}

async function submit(): Promise<void> {
    error.value = null;
    submitting.value = true;

    try {
        const admission = await store.start(form);
        await router.push({ name: 'admissions.show', params: { id: admission.id } });
    } catch (err) {
        error.value = extractError(err);
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <DashboardLayout>
        <h1 class="text-xl font-semibold text-slate-900">Start admission</h1>
        <p class="mt-1 text-sm text-slate-500">Creates the prisoner record and begins the admission workflow.</p>

        <p v-if="error" class="mt-3 text-sm text-red-600">{{ error }}</p>

        <form class="mt-6 max-w-lg space-y-4" @submit.prevent="submit">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">First name</label>
                    <input v-model="form.first_name" type="text" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Last name</label>
                    <input v-model="form.last_name" type="text" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Date of birth</label>
                    <input v-model="form.date_of_birth" type="date" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Gender</label>
                    <select v-model="form.gender" class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Expected release date (optional)</label>
                <input v-model="form.expected_release_date" type="date" class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Admission date</label>
                    <input v-model="form.admission_date" type="date" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Admission reason</label>
                    <input
                        v-model="form.admission_reason"
                        type="text"
                        required
                        placeholder="Remanded in custody"
                        class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                    />
                </div>
            </div>

            <button
                type="submit"
                :disabled="submitting"
                class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 disabled:opacity-50"
            >
                {{ submitting ? 'Starting…' : 'Start admission' }}
            </button>
        </form>
    </DashboardLayout>
</template>

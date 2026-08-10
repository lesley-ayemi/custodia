<script setup lang="ts">
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import DashboardLayout from '../../layouts/DashboardLayout.vue';
import { usePrisonerStore } from '../../stores/prisoner';
import type { PrisonerFormData } from '../../types/prisoner';

const store = usePrisonerStore();
const router = useRouter();

const form = reactive<PrisonerFormData>({
    first_name: '',
    last_name: '',
    date_of_birth: '',
    gender: 'male',
    admission_date: new Date().toISOString().slice(0, 10),
    expected_release_date: null,
});

const submitting = ref(false);
const error = ref<string | null>(null);

async function submit(): Promise<void> {
    submitting.value = true;
    error.value = null;

    try {
        const prisoner = await store.create(form);
        await router.push({ name: 'prisoners.show', params: { id: prisoner.id } });
    } catch {
        error.value = 'Please check the form for errors.';
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <DashboardLayout>
        <h1 class="text-xl font-semibold text-slate-900">Register prisoner</h1>

        <form class="mt-6 max-w-lg space-y-4" @submit.prevent="submit">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">First name</label>
                    <input
                        v-model="form.first_name"
                        type="text"
                        required
                        class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Last name</label>
                    <input
                        v-model="form.last_name"
                        type="text"
                        required
                        class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                    />
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Date of birth</label>
                    <input
                        v-model="form.date_of_birth"
                        type="date"
                        required
                        class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Gender</label>
                    <select v-model="form.gender" class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Admission date</label>
                    <input
                        v-model="form.admission_date"
                        type="date"
                        required
                        class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Expected release date</label>
                    <input
                        v-model="form.expected_release_date"
                        type="date"
                        class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                    />
                </div>
            </div>

            <p v-if="error" class="text-sm text-red-600">{{ error }}</p>

            <div class="flex gap-3">
                <button
                    type="submit"
                    :disabled="submitting"
                    class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 disabled:opacity-50"
                >
                    {{ submitting ? 'Saving…' : 'Register prisoner' }}
                </button>
                <router-link :to="{ name: 'prisoners.index' }" class="rounded-md px-4 py-2 text-sm font-medium text-slate-600 hover:text-slate-900">
                    Cancel
                </router-link>
            </div>
        </form>
    </DashboardLayout>
</template>

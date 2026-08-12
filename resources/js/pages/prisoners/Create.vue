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
        <h1 class="text-2xl font-bold text-slate-900">Register prisoner</h1>

        <form class="mt-6 max-w-lg space-y-4" @submit.prevent="submit">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="field-label">First name</label>
                    <input
                        v-model="form.first_name"
                        type="text"
                        required
                        class="mt-1 field-input"
                    />
                </div>
                <div>
                    <label class="field-label">Last name</label>
                    <input
                        v-model="form.last_name"
                        type="text"
                        required
                        class="mt-1 field-input"
                    />
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="field-label">Date of birth</label>
                    <input
                        v-model="form.date_of_birth"
                        type="date"
                        required
                        class="mt-1 field-input"
                    />
                </div>
                <div>
                    <label class="field-label">Gender</label>
                    <select v-model="form.gender" class="mt-1 field-input">
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="field-label">Admission date</label>
                    <input
                        v-model="form.admission_date"
                        type="date"
                        required
                        class="mt-1 field-input"
                    />
                </div>
                <div>
                    <label class="field-label">Expected release date</label>
                    <input
                        v-model="form.expected_release_date"
                        type="date"
                        class="mt-1 field-input"
                    />
                </div>
            </div>

            <p v-if="error" class="rounded-xl bg-red-50 px-3.5 py-2.5 text-sm text-red-700">{{ error }}</p>

            <div class="flex gap-3">
                <button
                    type="submit"
                    :disabled="submitting"
                    class="btn-primary disabled:opacity-50"
                >
                    {{ submitting ? 'Saving…' : 'Register prisoner' }}
                </button>
                <router-link :to="{ name: 'prisoners.index' }" class="rounded-xl px-4 py-2.5 text-sm font-medium text-slate-600 hover:text-slate-900">
                    Cancel
                </router-link>
            </div>
        </form>
    </DashboardLayout>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import DashboardLayout from '../../layouts/DashboardLayout.vue';
import { useUserStore } from '../../stores/user';
import type { StaffUserFormData } from '../../types/user';

const store = useUserStore();
const router = useRouter();

const form = reactive<StaffUserFormData>({
    name: '',
    email: '',
    password: '',
    role: 'officer',
});

const submitting = ref(false);
const error = ref<string | null>(null);

async function submit(): Promise<void> {
    submitting.value = true;
    error.value = null;

    try {
        const user = await store.create(form);
        await router.push({ name: 'users.show', params: { id: user.id } });
    } catch {
        error.value = 'Please check the form for errors — the email may already be in use.';
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <DashboardLayout>
        <h1 class="text-2xl font-bold text-slate-900">Add staff member</h1>

        <form class="mt-6 max-w-lg space-y-4" @submit.prevent="submit">
            <div>
                <label class="field-label">Name</label>
                <input
                    v-model="form.name"
                    type="text"
                    required
                    class="mt-1 field-input"
                />
            </div>

            <div>
                <label class="field-label">Email</label>
                <input
                    v-model="form.email"
                    type="email"
                    required
                    class="mt-1 field-input"
                />
            </div>

            <div>
                <label class="field-label">Password</label>
                <input
                    v-model="form.password"
                    type="password"
                    required
                    minlength="8"
                    class="mt-1 field-input"
                />
            </div>

            <div>
                <label class="field-label">Role</label>
                <select v-model="form.role" class="mt-1 field-input">
                    <option value="admin">Admin</option>
                    <option value="officer">Officer</option>
                    <option value="supervisor">Supervisor</option>
                    <option value="medical">Medical</option>
                </select>
            </div>

            <p v-if="error" class="rounded-xl bg-red-50 px-3.5 py-2.5 text-sm text-red-700">{{ error }}</p>

            <div class="flex gap-3">
                <button
                    type="submit"
                    :disabled="submitting"
                    class="btn-primary disabled:opacity-50"
                >
                    {{ submitting ? 'Saving…' : 'Add staff member' }}
                </button>
                <router-link :to="{ name: 'users.index' }" class="rounded-xl px-4 py-2.5 text-sm font-medium text-slate-600 hover:text-slate-900">
                    Cancel
                </router-link>
            </div>
        </form>
    </DashboardLayout>
</template>

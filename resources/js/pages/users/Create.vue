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
        <h1 class="text-xl font-semibold text-slate-900">Add staff member</h1>

        <form class="mt-6 max-w-lg space-y-4" @submit.prevent="submit">
            <div>
                <label class="block text-sm font-medium text-slate-700">Name</label>
                <input
                    v-model="form.name"
                    type="text"
                    required
                    class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Email</label>
                <input
                    v-model="form.email"
                    type="email"
                    required
                    class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Password</label>
                <input
                    v-model="form.password"
                    type="password"
                    required
                    minlength="8"
                    class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Role</label>
                <select v-model="form.role" class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    <option value="admin">Admin</option>
                    <option value="officer">Officer</option>
                    <option value="supervisor">Supervisor</option>
                    <option value="medical">Medical</option>
                </select>
            </div>

            <p v-if="error" class="text-sm text-red-600">{{ error }}</p>

            <div class="flex gap-3">
                <button
                    type="submit"
                    :disabled="submitting"
                    class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 disabled:opacity-50"
                >
                    {{ submitting ? 'Saving…' : 'Add staff member' }}
                </button>
                <router-link :to="{ name: 'users.index' }" class="rounded-md px-4 py-2 text-sm font-medium text-slate-600 hover:text-slate-900">
                    Cancel
                </router-link>
            </div>
        </form>
    </DashboardLayout>
</template>

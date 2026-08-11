<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import DashboardLayout from '../../layouts/DashboardLayout.vue';
import { useUserStore } from '../../stores/user';
import { useAuthStore } from '../../stores/auth';
import type { StaffUser } from '../../types/user';
import type { UserRole } from '../../types/auth';

const route = useRoute();
const router = useRouter();
const store = useUserStore();
const auth = useAuthStore();

const user = ref<StaffUser | null>(null);
const form = reactive<{ name: string; email: string; password: string; role: UserRole }>({
    name: '',
    email: '',
    password: '',
    role: 'officer',
});
const saving = ref(false);
const deactivating = ref(false);
const error = ref<string | null>(null);

const isSelf = computed(() => auth.user?.id === user.value?.id);

async function load(): Promise<void> {
    user.value = await store.fetchOne(Number(route.params.id));
    form.name = user.value.name;
    form.email = user.value.email;
    form.role = user.value.role;
    form.password = '';
}

async function save(): Promise<void> {
    if (!user.value) return;
    saving.value = true;
    error.value = null;

    try {
        const payload = { name: form.name, email: form.email, role: form.role, ...(form.password ? { password: form.password } : {}) };
        user.value = await store.update(user.value.id, payload);
        form.password = '';
    } catch {
        error.value = 'Please check the form for errors — the email may already be in use.';
    } finally {
        saving.value = false;
    }
}

async function deactivate(): Promise<void> {
    if (!user.value) return;
    deactivating.value = true;

    try {
        await store.deactivate(user.value.id);
        await router.push({ name: 'users.index' });
    } finally {
        deactivating.value = false;
    }
}

onMounted(load);
</script>

<template>
    <DashboardLayout>
        <div v-if="user">
            <h1 class="text-xl font-semibold text-slate-900">{{ user.name }}</h1>
            <p class="mt-1 text-sm text-slate-500">Staff member since {{ new Date(user.created_at ?? '').toLocaleDateString('en-GB') }}</p>

            <form class="mt-6 max-w-lg space-y-4 rounded-lg border border-slate-200 bg-white p-6" @submit.prevent="save">
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
                    <label class="block text-sm font-medium text-slate-700">Role</label>
                    <select v-model="form.role" class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                        <option value="admin">Admin</option>
                        <option value="officer">Officer</option>
                        <option value="supervisor">Supervisor</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">New password</label>
                    <input
                        v-model="form.password"
                        type="password"
                        minlength="8"
                        placeholder="Leave blank to keep current password"
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
                    v-if="!isSelf"
                    type="button"
                    :disabled="deactivating"
                    class="rounded-md border border-red-200 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50 disabled:opacity-50"
                    @click="deactivate"
                >
                    {{ deactivating ? 'Deactivating…' : 'Deactivate staff member' }}
                </button>
                <p v-else class="text-sm text-slate-400">You can't deactivate your own account.</p>
            </div>
        </div>
    </DashboardLayout>
</template>

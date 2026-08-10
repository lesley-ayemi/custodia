<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import AuthLayout from '../layouts/AuthLayout.vue';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const router = useRouter();

const email = ref('');
const password = ref('');
const error = ref<string | null>(null);
const submitting = ref(false);

async function submit(): Promise<void> {
    error.value = null;
    submitting.value = true;

    try {
        await auth.login(email.value, password.value);
        await router.push({ name: 'dashboard' });
    } catch {
        error.value = 'Invalid email or password.';
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <AuthLayout>
        <form class="space-y-4" @submit.prevent="submit">
            <div>
                <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                <input
                    id="email"
                    v-model="email"
                    type="email"
                    required
                    autofocus
                    class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none"
                />
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                <input
                    id="password"
                    v-model="password"
                    type="password"
                    required
                    class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none"
                />
            </div>

            <p v-if="error" class="text-sm text-red-600">{{ error }}</p>

            <button
                type="submit"
                :disabled="submitting"
                class="w-full rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 disabled:opacity-50"
            >
                {{ submitting ? 'Signing in…' : 'Sign in' }}
            </button>
        </form>
    </AuthLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import AuthLayout from '../layouts/AuthLayout.vue';
import { useAuthStore } from '../stores/auth';
import { Mail, Lock, LogIn } from '@lucide/vue';

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
        <h1 class="text-2xl font-bold text-slate-900">Welcome back</h1>
        <p class="mt-1.5 text-sm text-slate-500">Sign in to your Custodia account to continue.</p>

        <form class="mt-8 space-y-4" @submit.prevent="submit">
            <div>
                <label for="email" class="field-label">Email</label>
                <div class="relative">
                    <Mail :size="17" class="pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-slate-400" />
                    <input
                        id="email"
                        v-model="email"
                        type="email"
                        required
                        autofocus
                        placeholder="you@custodia.gov"
                        class="field-input pl-10"
                    />
                </div>
            </div>

            <div>
                <label for="password" class="field-label">Password</label>
                <div class="relative">
                    <Lock :size="17" class="pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-slate-400" />
                    <input id="password" v-model="password" type="password" required placeholder="••••••••" class="field-input pl-10" />
                </div>
            </div>

            <p v-if="error" class="rounded-xl bg-red-50 px-3.5 py-2.5 text-sm text-red-700">{{ error }}</p>

            <button type="submit" :disabled="submitting" class="btn-primary w-full">
                <LogIn :size="17" />
                {{ submitting ? 'Signing in…' : 'Sign in' }}
            </button>
        </form>
    </AuthLayout>
</template>

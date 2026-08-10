<script setup lang="ts">
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const router = useRouter();

async function logout(): Promise<void> {
    await auth.logout();
    await router.push({ name: 'login' });
}
</script>

<template>
    <div class="min-h-screen bg-slate-50">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
                <div class="flex items-center gap-8">
                    <span class="text-lg font-semibold text-slate-900">Custodia</span>
                    <nav class="flex items-center gap-4 text-sm text-slate-600">
                        <router-link :to="{ name: 'dashboard' }" class="hover:text-slate-900" active-class="font-semibold text-slate-900">
                            Dashboard
                        </router-link>
                        <router-link :to="{ name: 'prisoners.index' }" class="hover:text-slate-900" active-class="font-semibold text-slate-900">
                            Prisoners
                        </router-link>
                        <router-link :to="{ name: 'housing.index' }" class="hover:text-slate-900" active-class="font-semibold text-slate-900">
                            Housing
                        </router-link>
                    </nav>
                </div>
                <div class="flex items-center gap-4 text-sm">
                    <span class="text-slate-600">
                        {{ auth.user?.name }}
                        <span class="text-slate-400">· {{ auth.user?.role }}</span>
                    </span>
                    <button type="button" class="text-slate-500 hover:text-slate-900" @click="logout">Sign out</button>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-6 py-8">
            <slot />
        </main>
    </div>
</template>

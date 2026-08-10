import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import api, { ensureCsrfCookie } from '../services/api';
import type { AuthUser, UserRole } from '../types/auth';

export const useAuthStore = defineStore('auth', () => {
    const user = ref<AuthUser | null>(null);
    const initialized = ref(false);

    const isAuthenticated = computed(() => user.value !== null);

    function hasRole(...roles: UserRole[]): boolean {
        return user.value !== null && roles.includes(user.value.role);
    }

    async function login(email: string, password: string): Promise<void> {
        await ensureCsrfCookie();
        const response = await api.post<AuthUser>('/api/login', { email, password });
        user.value = response.data;
    }

    async function logout(): Promise<void> {
        await api.post('/api/logout');
        user.value = null;
    }

    async function fetchUser(): Promise<void> {
        try {
            const response = await api.get<AuthUser>('/api/me');
            user.value = response.data;
        } catch {
            user.value = null;
        } finally {
            initialized.value = true;
        }
    }

    return { user, initialized, isAuthenticated, hasRole, login, logout, fetchUser };
});

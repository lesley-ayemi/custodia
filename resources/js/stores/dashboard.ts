import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '../services/api';
import type { DashboardData } from '../types/dashboard';

export const useDashboardStore = defineStore('dashboard', () => {
    const data = ref<DashboardData | null>(null);
    const loading = ref(false);

    async function fetchDashboard(): Promise<void> {
        loading.value = true;

        try {
            const response = await api.get<DashboardData>('/api/dashboard');
            data.value = response.data;
        } finally {
            loading.value = false;
        }
    }

    return { data, loading, fetchDashboard };
});

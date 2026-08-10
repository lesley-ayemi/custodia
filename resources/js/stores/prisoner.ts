import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '../services/api';
import type { PaginatedResponse, Prisoner, PrisonerFormData } from '../types/prisoner';

export const usePrisonerStore = defineStore('prisoner', () => {
    const prisoners = ref<Prisoner[]>([]);
    const currentPage = ref(1);
    const lastPage = ref(1);
    const total = ref(0);
    const loading = ref(false);

    async function fetchList(search = '', page = 1): Promise<void> {
        loading.value = true;

        try {
            const response = await api.get<PaginatedResponse<Prisoner>>('/api/prisoners', {
                params: { search: search || undefined, page },
            });

            prisoners.value = response.data.data;
            currentPage.value = response.data.meta.current_page;
            lastPage.value = response.data.meta.last_page;
            total.value = response.data.meta.total;
        } finally {
            loading.value = false;
        }
    }

    async function fetchOne(id: number): Promise<Prisoner> {
        const response = await api.get<Prisoner>(`/api/prisoners/${id}`);
        return response.data;
    }

    async function create(data: PrisonerFormData): Promise<Prisoner> {
        const response = await api.post<Prisoner>('/api/prisoners', data);
        return response.data;
    }

    async function update(id: number, data: Partial<PrisonerFormData>): Promise<Prisoner> {
        const response = await api.put<Prisoner>(`/api/prisoners/${id}`, data);
        return response.data;
    }

    async function archive(id: number): Promise<void> {
        await api.post(`/api/prisoners/${id}/archive`);
    }

    return { prisoners, currentPage, lastPage, total, loading, fetchList, fetchOne, create, update, archive };
});

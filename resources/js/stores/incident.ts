import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '../services/api';
import type { PaginatedResponse } from '../types/prisoner';
import type { Incident, IncidentFormData, IncidentUpdateFormData } from '../types/incident';

export const useIncidentStore = defineStore('incident', () => {
    const incidents = ref<Incident[]>([]);
    const currentPage = ref(1);
    const lastPage = ref(1);
    const total = ref(0);
    const loading = ref(false);

    async function fetchList(status = '', page = 1): Promise<void> {
        loading.value = true;

        try {
            const response = await api.get<PaginatedResponse<Incident>>('/api/incidents', {
                params: { status: status || undefined, page },
            });

            incidents.value = response.data.data;
            currentPage.value = response.data.meta.current_page;
            lastPage.value = response.data.meta.last_page;
            total.value = response.data.meta.total;
        } finally {
            loading.value = false;
        }
    }

    async function fetchOne(id: number): Promise<Incident> {
        const response = await api.get<Incident>(`/api/incidents/${id}`);
        return response.data;
    }

    async function create(data: IncidentFormData): Promise<Incident> {
        const response = await api.post<Incident>('/api/incidents', data);
        return response.data;
    }

    async function update(id: number, data: Partial<IncidentUpdateFormData>): Promise<Incident> {
        const response = await api.put<Incident>(`/api/incidents/${id}`, data);
        return response.data;
    }

    async function remove(id: number): Promise<void> {
        await api.delete(`/api/incidents/${id}`);
    }

    async function markUnderReview(id: number): Promise<Incident> {
        const response = await api.post<Incident>(`/api/incidents/${id}/review`);
        return response.data;
    }

    async function resolve(id: number): Promise<Incident> {
        const response = await api.post<Incident>(`/api/incidents/${id}/resolve`);
        return response.data;
    }

    return { incidents, currentPage, lastPage, total, loading, fetchList, fetchOne, create, update, remove, markUnderReview, resolve };
});

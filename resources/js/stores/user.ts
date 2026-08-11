import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '../services/api';
import type { PaginatedResponse } from '../types/prisoner';
import type { StaffUser, StaffUserFormData } from '../types/user';

export type SortField = 'name' | 'email' | 'role' | 'created_at';
export type SortDirection = 'asc' | 'desc';

export const useUserStore = defineStore('user', () => {
    const users = ref<StaffUser[]>([]);
    const currentPage = ref(1);
    const lastPage = ref(1);
    const total = ref(0);
    const loading = ref(false);

    async function fetchList(search = '', sort: SortField = 'name', direction: SortDirection = 'asc', page = 1): Promise<void> {
        loading.value = true;

        try {
            const response = await api.get<PaginatedResponse<StaffUser>>('/api/users', {
                params: { search: search || undefined, sort, direction, page },
            });

            users.value = response.data.data;
            currentPage.value = response.data.meta.current_page;
            lastPage.value = response.data.meta.last_page;
            total.value = response.data.meta.total;
        } finally {
            loading.value = false;
        }
    }

    async function fetchOne(id: number): Promise<StaffUser> {
        const response = await api.get<StaffUser>(`/api/users/${id}`);
        return response.data;
    }

    async function create(data: StaffUserFormData): Promise<StaffUser> {
        const response = await api.post<StaffUser>('/api/users', data);
        return response.data;
    }

    async function update(id: number, data: Partial<StaffUserFormData>): Promise<StaffUser> {
        const response = await api.put<StaffUser>(`/api/users/${id}`, data);
        return response.data;
    }

    async function deactivate(id: number): Promise<void> {
        await api.delete(`/api/users/${id}`);
    }

    return { users, currentPage, lastPage, total, loading, fetchList, fetchOne, create, update, deactivate };
});

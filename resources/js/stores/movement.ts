import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '../services/api';
import type { Movement, MovementFormData } from '../types/movement';

export const useMovementStore = defineStore('movement', () => {
    const movements = ref<Movement[]>([]);
    const upcoming = ref<Movement[]>([]);
    const loading = ref(false);

    async function fetchForPrisoner(prisonerId: number): Promise<void> {
        loading.value = true;

        try {
            const response = await api.get<Movement[]>(`/api/prisoners/${prisonerId}/movements`);
            movements.value = response.data;
        } finally {
            loading.value = false;
        }
    }

    async function fetchUpcoming(): Promise<void> {
        loading.value = true;

        try {
            const response = await api.get<Movement[]>('/api/movements/upcoming');
            upcoming.value = response.data;
        } finally {
            loading.value = false;
        }
    }

    async function request(prisonerId: number, data: MovementFormData): Promise<Movement> {
        const response = await api.post<Movement>(`/api/prisoners/${prisonerId}/movements`, data);
        return response.data;
    }

    async function approve(id: number): Promise<void> {
        await api.post(`/api/movements/${id}/approve`);
    }

    async function depart(id: number): Promise<void> {
        await api.post(`/api/movements/${id}/depart`);
    }

    async function arrive(id: number): Promise<void> {
        await api.post(`/api/movements/${id}/arrive`);
    }

    async function markReturned(id: number): Promise<void> {
        await api.post(`/api/movements/${id}/return`);
    }

    async function cancel(id: number): Promise<void> {
        await api.post(`/api/movements/${id}/cancel`);
    }

    return {
        movements,
        upcoming,
        loading,
        fetchForPrisoner,
        fetchUpcoming,
        request,
        approve,
        depart,
        arrive,
        markReturned,
        cancel,
    };
});

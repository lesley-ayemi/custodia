import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '../services/api';
import type { PaginatedResponse } from '../types/prisoner';
import type { Admission, StartAdmissionFormData } from '../types/admission';

export const useAdmissionStore = defineStore('admission', () => {
    const admissions = ref<Admission[]>([]);
    const current = ref<Admission | null>(null);
    const loading = ref(false);

    async function fetchList(status = ''): Promise<void> {
        loading.value = true;

        try {
            const response = await api.get<PaginatedResponse<Admission>>('/api/admissions', {
                params: { status: status || undefined },
            });
            admissions.value = response.data.data;
        } finally {
            loading.value = false;
        }
    }

    async function fetchOne(id: number): Promise<void> {
        loading.value = true;

        try {
            const response = await api.get<Admission>(`/api/admissions/${id}`);
            current.value = response.data;
        } finally {
            loading.value = false;
        }
    }

    async function start(data: StartAdmissionFormData): Promise<Admission> {
        const response = await api.post<Admission>('/api/admissions', data);
        return response.data;
    }

    async function recordLegalAuthority(id: number, reference: string): Promise<Admission> {
        const response = await api.post<Admission>(`/api/admissions/${id}/legal-authority`, { reference });
        return response.data;
    }

    async function recordAssessment(id: number, notes: string): Promise<Admission> {
        const response = await api.post<Admission>(`/api/admissions/${id}/assessment`, { notes });
        return response.data;
    }

    async function recordClassification(id: number, classification: string): Promise<Admission> {
        const response = await api.post<Admission>(`/api/admissions/${id}/classification`, { classification });
        return response.data;
    }

    async function advanceToMedical(id: number): Promise<Admission> {
        const response = await api.post<Admission>(`/api/admissions/${id}/advance-to-medical`);
        return response.data;
    }

    async function completeMedical(id: number): Promise<Admission> {
        const response = await api.post<Admission>(`/api/admissions/${id}/complete-medical`);
        return response.data;
    }

    async function completeHousing(id: number): Promise<Admission> {
        const response = await api.post<Admission>(`/api/admissions/${id}/complete-housing`);
        return response.data;
    }

    return {
        admissions,
        current,
        loading,
        fetchList,
        fetchOne,
        start,
        recordLegalAuthority,
        recordAssessment,
        recordClassification,
        advanceToMedical,
        completeMedical,
        completeHousing,
    };
});

import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '../services/api';
import type { CourtCase, CourtCaseFormData, CourtHearing, CourtHearingFormData, LegalRepresentative } from '../types/court';

export const useCourtStore = defineStore('court', () => {
    const cases = ref<CourtCase[]>([]);
    const upcomingHearings = ref<CourtHearing[]>([]);
    const legalRepresentatives = ref<LegalRepresentative[]>([]);
    const loading = ref(false);

    async function fetchCasesForPrisoner(prisonerId: number): Promise<void> {
        loading.value = true;

        try {
            const response = await api.get<CourtCase[]>(`/api/prisoners/${prisonerId}/court-cases`);
            cases.value = response.data;
        } finally {
            loading.value = false;
        }
    }

    async function fetchUpcomingHearings(): Promise<void> {
        loading.value = true;

        try {
            const response = await api.get<CourtHearing[]>('/api/court-hearings/upcoming');
            upcomingHearings.value = response.data;
        } finally {
            loading.value = false;
        }
    }

    async function fetchLegalRepresentatives(): Promise<void> {
        const response = await api.get<LegalRepresentative[]>('/api/legal-representatives');
        legalRepresentatives.value = response.data;
    }

    async function createLegalRepresentative(data: { name: string; firm_name?: string; phone?: string; email?: string }): Promise<LegalRepresentative> {
        const response = await api.post<LegalRepresentative>('/api/legal-representatives', data);
        legalRepresentatives.value.push(response.data);
        return response.data;
    }

    async function openCase(prisonerId: number, data: CourtCaseFormData): Promise<CourtCase> {
        const response = await api.post<CourtCase>(`/api/prisoners/${prisonerId}/court-cases`, data);
        return response.data;
    }

    async function scheduleHearing(courtCaseId: number, data: CourtHearingFormData): Promise<CourtHearing> {
        const response = await api.post<CourtHearing>(`/api/court-cases/${courtCaseId}/hearings`, data);
        return response.data;
    }

    return {
        cases,
        upcomingHearings,
        legalRepresentatives,
        loading,
        fetchCasesForPrisoner,
        fetchUpcomingHearings,
        fetchLegalRepresentatives,
        createLegalRepresentative,
        openCase,
        scheduleHearing,
    };
});

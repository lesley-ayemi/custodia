import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '../services/api';
import type { Programme, ProgrammeEnrolment, ProgrammeFormData } from '../types/programme';

export const useProgrammeStore = defineStore('programme', () => {
    const programmes = ref<Programme[]>([]);
    const enrolments = ref<ProgrammeEnrolment[]>([]);
    const loading = ref(false);

    async function fetchProgrammes(): Promise<void> {
        loading.value = true;

        try {
            const response = await api.get<Programme[]>('/api/programmes');
            programmes.value = response.data;
        } finally {
            loading.value = false;
        }
    }

    async function fetchEnrolmentsForPrisoner(prisonerId: number): Promise<void> {
        loading.value = true;

        try {
            const response = await api.get<ProgrammeEnrolment[]>(`/api/prisoners/${prisonerId}/programme-enrolments`);
            enrolments.value = response.data;
        } finally {
            loading.value = false;
        }
    }

    async function createProgramme(data: ProgrammeFormData): Promise<Programme> {
        const response = await api.post<Programme>('/api/programmes', data);
        return response.data;
    }

    async function updateProgramme(id: number, data: Partial<ProgrammeFormData>): Promise<Programme> {
        const response = await api.put<Programme>(`/api/programmes/${id}`, data);
        return response.data;
    }

    async function deleteProgramme(id: number): Promise<void> {
        await api.delete(`/api/programmes/${id}`);
    }

    async function enrol(prisonerId: number, programmeId: number, enrolledAt: string): Promise<ProgrammeEnrolment> {
        const response = await api.post<ProgrammeEnrolment>(`/api/prisoners/${prisonerId}/programme-enrolments`, {
            programme_id: programmeId,
            enrolled_at: enrolledAt,
        });
        return response.data;
    }

    async function recordAttendance(
        enrolmentId: number,
        data: { session_date: string; attended: boolean; notes: string | null },
    ): Promise<void> {
        await api.post(`/api/programme-enrolments/${enrolmentId}/attendance`, data);
    }

    async function complete(enrolmentId: number): Promise<void> {
        await api.post(`/api/programme-enrolments/${enrolmentId}/complete`);
    }

    async function withdraw(enrolmentId: number, reason: string | null): Promise<void> {
        await api.post(`/api/programme-enrolments/${enrolmentId}/withdraw`, { reason });
    }

    return {
        programmes,
        enrolments,
        loading,
        fetchProgrammes,
        fetchEnrolmentsForPrisoner,
        createProgramme,
        updateProgramme,
        deleteProgramme,
        enrol,
        recordAttendance,
        complete,
        withdraw,
    };
});

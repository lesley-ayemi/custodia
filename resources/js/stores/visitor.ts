import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '../services/api';
import type { Visit, VisitorFormData, VisitRequest, Visitor } from '../types/visitor';

export const useVisitorStore = defineStore('visitor', () => {
    const visitors = ref<Visitor[]>([]);
    const requests = ref<VisitRequest[]>([]);
    const visits = ref<Visit[]>([]);
    const upcomingVisits = ref<Visit[]>([]);
    const loading = ref(false);

    async function fetchVisitors(): Promise<void> {
        loading.value = true;

        try {
            const response = await api.get<Visitor[]>('/api/visitors');
            visitors.value = response.data;
        } finally {
            loading.value = false;
        }
    }

    async function fetchRequestsForPrisoner(prisonerId: number): Promise<void> {
        const response = await api.get<VisitRequest[]>(`/api/prisoners/${prisonerId}/visit-requests`);
        requests.value = response.data;
    }

    async function fetchVisitsForPrisoner(prisonerId: number): Promise<void> {
        const response = await api.get<Visit[]>(`/api/prisoners/${prisonerId}/visits`);
        visits.value = response.data;
    }

    async function fetchUpcomingVisits(): Promise<void> {
        loading.value = true;

        try {
            const response = await api.get<Visit[]>('/api/visits/upcoming');
            upcomingVisits.value = response.data;
        } finally {
            loading.value = false;
        }
    }

    async function registerVisitor(data: VisitorFormData): Promise<Visitor> {
        const response = await api.post<Visitor>('/api/visitors', data);
        return response.data;
    }

    async function requestVisit(data: {
        visitor_id: number;
        prisoner_id: number;
        relationship: string;
        requested_visit_date: string;
    }): Promise<VisitRequest> {
        const response = await api.post<VisitRequest>('/api/visit-requests', data);
        return response.data;
    }

    async function approveRequest(id: number, scheduledAt: string): Promise<VisitRequest> {
        const response = await api.post<VisitRequest>(`/api/visit-requests/${id}/approve`, { scheduled_at: scheduledAt });
        return response.data;
    }

    async function rejectRequest(id: number, reason: string | null): Promise<VisitRequest> {
        const response = await api.post<VisitRequest>(`/api/visit-requests/${id}/reject`, { reason });
        return response.data;
    }

    async function checkIn(visitId: number): Promise<Visit> {
        const response = await api.post<Visit>(`/api/visits/${visitId}/check-in`);
        return response.data;
    }

    async function checkOut(visitId: number, notes: string | null): Promise<Visit> {
        const response = await api.post<Visit>(`/api/visits/${visitId}/check-out`, { notes });
        return response.data;
    }

    async function cancelVisit(visitId: number): Promise<Visit> {
        const response = await api.post<Visit>(`/api/visits/${visitId}/cancel`);
        return response.data;
    }

    return {
        visitors,
        requests,
        visits,
        upcomingVisits,
        loading,
        fetchVisitors,
        fetchRequestsForPrisoner,
        fetchVisitsForPrisoner,
        fetchUpcomingVisits,
        registerVisitor,
        requestVisit,
        approveRequest,
        rejectRequest,
        checkIn,
        checkOut,
        cancelVisit,
    };
});

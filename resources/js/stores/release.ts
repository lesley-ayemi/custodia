import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '../services/api';
import type { PaginatedResponse } from '../types/prisoner';
import type { ReleaseReview } from '../types/release';

export const useReleaseStore = defineStore('release', () => {
    const reviews = ref<ReleaseReview[]>([]);
    const prisonerReviews = ref<ReleaseReview[]>([]);
    const loading = ref(false);

    async function fetchAll(status = ''): Promise<void> {
        loading.value = true;

        try {
            const response = await api.get<PaginatedResponse<ReleaseReview>>('/api/release-reviews', {
                params: { status: status || undefined },
            });
            reviews.value = response.data.data;
        } finally {
            loading.value = false;
        }
    }

    async function fetchForPrisoner(prisonerId: number): Promise<void> {
        loading.value = true;

        try {
            const response = await api.get<ReleaseReview[]>(`/api/prisoners/${prisonerId}/release-reviews`);
            prisonerReviews.value = response.data;
        } finally {
            loading.value = false;
        }
    }

    async function schedule(prisonerId: number): Promise<ReleaseReview> {
        const response = await api.post<ReleaseReview>(`/api/prisoners/${prisonerId}/release-reviews`);
        return response.data;
    }

    async function completeStep(reviewId: number, endpoint: string, notes: string | null): Promise<ReleaseReview> {
        const response = await api.post<ReleaseReview>(`/api/release-reviews/${reviewId}/${endpoint}`, { notes });
        return response.data;
    }

    async function cancel(reviewId: number, reason: string | null): Promise<ReleaseReview> {
        const response = await api.post<ReleaseReview>(`/api/release-reviews/${reviewId}/cancel`, { reason });
        return response.data;
    }

    return { reviews, prisonerReviews, loading, fetchAll, fetchForPrisoner, schedule, completeStep, cancel };
});

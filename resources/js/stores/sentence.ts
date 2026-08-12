import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '../services/api';
import type { Sentence, SentenceFormData } from '../types/sentence';

export const useSentenceStore = defineStore('sentence', () => {
    const sentences = ref<Sentence[]>([]);
    const loading = ref(false);

    async function fetchForPrisoner(prisonerId: number): Promise<void> {
        loading.value = true;

        try {
            const response = await api.get<Sentence[]>(`/api/prisoners/${prisonerId}/sentences`);
            sentences.value = response.data;
        } finally {
            loading.value = false;
        }
    }

    async function record(prisonerId: number, data: SentenceFormData): Promise<Sentence> {
        const response = await api.post<Sentence>(`/api/prisoners/${prisonerId}/sentences`, data);
        return response.data;
    }

    return {
        sentences,
        loading,
        fetchForPrisoner,
        record,
    };
});

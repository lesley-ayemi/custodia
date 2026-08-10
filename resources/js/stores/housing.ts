import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '../services/api';
import type { Block, HousingAssignment } from '../types/housing';

export const useHousingStore = defineStore('housing', () => {
    const blocks = ref<Block[]>([]);
    const loading = ref(false);

    async function fetchBlocks(): Promise<void> {
        loading.value = true;

        try {
            const response = await api.get<Block[]>('/api/blocks');
            blocks.value = response.data;
        } finally {
            loading.value = false;
        }
    }

    async function assign(prisonerId: number, cellId: number): Promise<HousingAssignment> {
        const response = await api.post<HousingAssignment>('/api/housing-assignments', {
            prisoner_id: prisonerId,
            cell_id: cellId,
        });
        return response.data;
    }

    async function fetchHistory(prisonerId: number): Promise<HousingAssignment[]> {
        const response = await api.get<HousingAssignment[]>(`/api/prisoners/${prisonerId}/housing-history`);
        return response.data;
    }

    return { blocks, loading, fetchBlocks, assign, fetchHistory };
});

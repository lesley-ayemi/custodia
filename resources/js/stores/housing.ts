import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '../services/api';
import type { Block, CellSummary, HousingAssignment } from '../types/housing';

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

    async function createBlock(name: string): Promise<Block> {
        const response = await api.post<Block>('/api/blocks', { name });
        return response.data;
    }

    async function renameBlock(id: number, name: string): Promise<Block> {
        const response = await api.put<Block>(`/api/blocks/${id}`, { name });
        return response.data;
    }

    async function deleteBlock(id: number): Promise<void> {
        await api.delete(`/api/blocks/${id}`);
    }

    async function createCell(blockId: number, code: string, capacity: number): Promise<CellSummary> {
        const response = await api.post<CellSummary>('/api/cells', { block_id: blockId, code, capacity });
        return response.data;
    }

    async function updateCell(id: number, data: { code?: string; capacity?: number }): Promise<CellSummary> {
        const response = await api.put<CellSummary>(`/api/cells/${id}`, data);
        return response.data;
    }

    async function deleteCell(id: number): Promise<void> {
        await api.delete(`/api/cells/${id}`);
    }

    return {
        blocks,
        loading,
        fetchBlocks,
        assign,
        fetchHistory,
        createBlock,
        renameBlock,
        deleteBlock,
        createCell,
        updateCell,
        deleteCell,
    };
});

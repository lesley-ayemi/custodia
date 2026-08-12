import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '../services/api';
import type { Block, CellSummary, HousingAssignment, Wing } from '../types/housing';

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

    async function createWing(blockId: number, name: string): Promise<Wing> {
        const response = await api.post<Wing>('/api/wings', { block_id: blockId, name });
        return response.data;
    }

    async function renameWing(id: number, name: string): Promise<Wing> {
        const response = await api.put<Wing>(`/api/wings/${id}`, { name });
        return response.data;
    }

    async function deleteWing(id: number): Promise<void> {
        await api.delete(`/api/wings/${id}`);
    }

    async function createCell(wingId: number, code: string, capacity: number): Promise<CellSummary> {
        const response = await api.post<CellSummary>('/api/cells', { wing_id: wingId, code, capacity });
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
        createWing,
        renameWing,
        deleteWing,
        createCell,
        updateCell,
        deleteCell,
    };
});

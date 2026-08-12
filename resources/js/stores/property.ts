import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '../services/api';
import type { PropertyItem, PropertyItemDraft } from '../types/property';

export const usePropertyStore = defineStore('property', () => {
    const items = ref<PropertyItem[]>([]);
    const loading = ref(false);

    async function fetchForPrisoner(prisonerId: number): Promise<void> {
        loading.value = true;

        try {
            const response = await api.get<PropertyItem[]>(`/api/prisoners/${prisonerId}/property`);
            items.value = response.data;
        } finally {
            loading.value = false;
        }
    }

    async function receiveBag(prisonerId: number, items: PropertyItemDraft[]): Promise<PropertyItem[]> {
        const response = await api.post<PropertyItem[]>(`/api/prisoners/${prisonerId}/property`, { items });
        return response.data;
    }

    async function release(itemId: number, releasedTo: string): Promise<PropertyItem> {
        const response = await api.post<PropertyItem>(`/api/property-items/${itemId}/release`, { released_to: releasedTo });
        return response.data;
    }

    return { items, loading, fetchForPrisoner, receiveBag, release };
});

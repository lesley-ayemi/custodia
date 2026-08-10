import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '../services/api';
import type { PaginatedResponse } from '../types/prisoner';
import type { AuditLog } from '../types/audit';

export const useAuditStore = defineStore('audit', () => {
    const logs = ref<AuditLog[]>([]);
    const currentPage = ref(1);
    const lastPage = ref(1);
    const loading = ref(false);

    async function fetchList(page = 1): Promise<void> {
        loading.value = true;

        try {
            const response = await api.get<PaginatedResponse<AuditLog>>('/api/audit-logs', { params: { page } });
            logs.value = response.data.data;
            currentPage.value = response.data.meta.current_page;
            lastPage.value = response.data.meta.last_page;
        } finally {
            loading.value = false;
        }
    }

    return { logs, currentPage, lastPage, loading, fetchList };
});

<script setup lang="ts">
import { onMounted, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import DashboardLayout from '../../layouts/DashboardLayout.vue';
import StatusBadge from '../../components/StatusBadge.vue';
import DataTable from '../../components/DataTable.vue';
import type { DataTableColumn } from '../../components/DataTable.vue';
import { usePrisonerStore } from '../../stores/prisoner';
import { useAuthStore } from '../../stores/auth';
import type { Prisoner } from '../../types/prisoner';

const store = usePrisonerStore();
const auth = useAuthStore();
const router = useRouter();
const search = ref('');
let searchTimeout: ReturnType<typeof setTimeout> | undefined;

const columns: DataTableColumn[] = [
    { key: 'prisoner_number', label: 'Prisoner #', sortable: true },
    { key: 'full_name', label: 'Name', sortable: true },
    { key: 'admission_date', label: 'Admission', sortable: true },
    { key: 'status', label: 'Status' },
];

onMounted(() => store.fetchList());

watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => store.fetchList(value, 1), 300);
});

function goToPage(page: number): void {
    store.fetchList(search.value, page);
}

function openPrisoner(row: Record<string, unknown>): void {
    router.push({ name: 'prisoners.show', params: { id: (row as unknown as Prisoner).id } });
}
</script>

<template>
    <DashboardLayout>
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-slate-900">Prisoners</h1>
            <router-link
                v-if="auth.hasRole('officer', 'admin')"
                :to="{ name: 'prisoners.create' }"
                class="btn-primary"
            >
                Register prisoner
            </router-link>
        </div>

        <input
            v-model="search"
            type="search"
            placeholder="Search prisoners…"
            class="mt-4 field-input max-w-sm"
        />

        <div class="mt-4">
            <DataTable
                :columns="columns"
                :rows="store.prisoners as unknown as Record<string, unknown>[]"
                :loading="store.loading"
                empty-message="No prisoners found."
                clickable-rows
                @row-click="openPrisoner"
            >
                <template #cell-prisoner_number="{ value }">
                    <span class="font-medium text-slate-900">{{ value }}</span>
                </template>
                <template #cell-status="{ row }">
                    <StatusBadge :status="(row as unknown as Prisoner).status" />
                </template>
            </DataTable>
        </div>

        <div v-if="store.lastPage > 1" class="mt-4 flex items-center justify-between text-sm text-slate-600">
            <span>Page {{ store.currentPage }} of {{ store.lastPage }} · {{ store.total }} total</span>
            <div class="flex gap-2">
                <button
                    type="button"
                    :disabled="store.currentPage <= 1"
                    class="btn-secondary-sm"
                    @click="goToPage(store.currentPage - 1)"
                >
                    Previous
                </button>
                <button
                    type="button"
                    :disabled="store.currentPage >= store.lastPage"
                    class="btn-secondary-sm"
                    @click="goToPage(store.currentPage + 1)"
                >
                    Next
                </button>
            </div>
        </div>
    </DashboardLayout>
</template>

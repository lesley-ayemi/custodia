<script setup lang="ts">
import { onMounted, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import DashboardLayout from '../../layouts/DashboardLayout.vue';
import DataTable from '../../components/DataTable.vue';
import type { DataTableColumn } from '../../components/DataTable.vue';
import { useUserStore, type SortDirection, type SortField } from '../../stores/user';
import type { StaffUser } from '../../types/user';

const store = useUserStore();
const router = useRouter();
const search = ref('');
const sort = ref<SortField>('name');
const direction = ref<SortDirection>('asc');
let searchTimeout: ReturnType<typeof setTimeout> | undefined;

const columns: DataTableColumn[] = [
    { key: 'name', label: 'Name', sortable: true },
    { key: 'email', label: 'Email', sortable: true },
    { key: 'role', label: 'Role', sortable: true },
    { key: 'created_at', label: 'Joined', sortable: true },
];

function load(page = 1): void {
    store.fetchList(search.value, sort.value, direction.value, page);
}

function onSort({ key, direction: dir }: { key: string; direction: 'asc' | 'desc' }): void {
    sort.value = key as SortField;
    direction.value = dir;
    load();
}

function formatDate(value: string | null): string {
    if (!value) return '—';
    return new Date(value).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
}

function openUser(row: Record<string, unknown>): void {
    router.push({ name: 'users.show', params: { id: (row as unknown as StaffUser).id } });
}

onMounted(() => load());

watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => load(1), 300);
});
</script>

<template>
    <DashboardLayout>
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-slate-900">Staff</h1>
            <router-link
                :to="{ name: 'users.create' }"
                class="btn-primary"
            >
                Add staff member
            </router-link>
        </div>

        <input
            v-model="search"
            type="search"
            placeholder="Search staff…"
            class="mt-4 field-input max-w-sm"
        />

        <div class="mt-4">
            <DataTable
                :columns="columns"
                :rows="store.users as unknown as Record<string, unknown>[]"
                :loading="store.loading"
                empty-message="No staff found."
                clickable-rows
                server-sort
                @row-click="openUser"
                @sort="onSort"
            >
                <template #cell-name="{ value }">
                    <span class="font-medium text-slate-900">{{ value }}</span>
                </template>
                <template #cell-email="{ value }">
                    <span class="text-slate-600">{{ value }}</span>
                </template>
                <template #cell-role="{ value }">
                    <span class="text-slate-600 capitalize">{{ value }}</span>
                </template>
                <template #cell-created_at="{ value }">
                    <span class="text-slate-500">{{ formatDate(value as string) }}</span>
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
                    @click="load(store.currentPage - 1)"
                >
                    Previous
                </button>
                <button
                    type="button"
                    :disabled="store.currentPage >= store.lastPage"
                    class="btn-secondary-sm"
                    @click="load(store.currentPage + 1)"
                >
                    Next
                </button>
            </div>
        </div>
    </DashboardLayout>
</template>

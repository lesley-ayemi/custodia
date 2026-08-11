<script setup lang="ts">
import { onMounted, ref, watch } from 'vue';
import DashboardLayout from '../../layouts/DashboardLayout.vue';
import { useUserStore, type SortDirection, type SortField } from '../../stores/user';

const store = useUserStore();
const search = ref('');
const sort = ref<SortField>('name');
const direction = ref<SortDirection>('asc');
let searchTimeout: ReturnType<typeof setTimeout> | undefined;

const columns: { field: SortField; label: string }[] = [
    { field: 'name', label: 'Name' },
    { field: 'email', label: 'Email' },
    { field: 'role', label: 'Role' },
    { field: 'created_at', label: 'Joined' },
];

function load(page = 1): void {
    store.fetchList(search.value, sort.value, direction.value, page);
}

function toggleSort(field: SortField): void {
    if (sort.value === field) {
        direction.value = direction.value === 'asc' ? 'desc' : 'asc';
    } else {
        sort.value = field;
        direction.value = 'asc';
    }
    load();
}

function formatDate(value: string | null): string {
    if (!value) return '—';
    return new Date(value).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
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
            <h1 class="text-xl font-semibold text-slate-900">Staff</h1>
            <router-link
                :to="{ name: 'users.create' }"
                class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800"
            >
                Add staff member
            </router-link>
        </div>

        <input
            v-model="search"
            type="search"
            placeholder="Search staff…"
            class="mt-4 block w-full max-w-sm rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none"
        />

        <div class="mt-4 overflow-hidden rounded-lg border border-slate-200 bg-white">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-medium tracking-wider text-slate-500 uppercase">
                    <tr>
                        <th v-for="column in columns" :key="column.field" class="px-4 py-3">
                            <button type="button" class="flex items-center gap-1 hover:text-slate-900" @click="toggleSort(column.field)">
                                {{ column.label }}
                                <span v-if="sort === column.field">{{ direction === 'asc' ? '↑' : '↓' }}</span>
                            </button>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr
                        v-for="user in store.users"
                        :key="user.id"
                        class="cursor-pointer hover:bg-slate-50"
                        @click="$router.push({ name: 'users.show', params: { id: user.id } })"
                    >
                        <td class="px-4 py-3 font-medium text-slate-900">{{ user.name }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ user.email }}</td>
                        <td class="px-4 py-3 text-slate-600 capitalize">{{ user.role }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ formatDate(user.created_at) }}</td>
                    </tr>
                    <tr v-if="!store.loading && store.users.length === 0">
                        <td colspan="4" class="px-4 py-6 text-center text-slate-500">No staff found.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="store.lastPage > 1" class="mt-4 flex items-center justify-between text-sm text-slate-600">
            <span>Page {{ store.currentPage }} of {{ store.lastPage }} · {{ store.total }} total</span>
            <div class="flex gap-2">
                <button
                    type="button"
                    :disabled="store.currentPage <= 1"
                    class="rounded-md border border-slate-300 px-3 py-1 disabled:opacity-40"
                    @click="load(store.currentPage - 1)"
                >
                    Previous
                </button>
                <button
                    type="button"
                    :disabled="store.currentPage >= store.lastPage"
                    class="rounded-md border border-slate-300 px-3 py-1 disabled:opacity-40"
                    @click="load(store.currentPage + 1)"
                >
                    Next
                </button>
            </div>
        </div>
    </DashboardLayout>
</template>

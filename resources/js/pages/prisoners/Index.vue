<script setup lang="ts">
import { onMounted, ref, watch } from 'vue';
import DashboardLayout from '../../layouts/DashboardLayout.vue';
import StatusBadge from '../../components/StatusBadge.vue';
import { usePrisonerStore } from '../../stores/prisoner';
import { useAuthStore } from '../../stores/auth';

const store = usePrisonerStore();
const auth = useAuthStore();
const search = ref('');
let searchTimeout: ReturnType<typeof setTimeout> | undefined;

onMounted(() => store.fetchList());

watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => store.fetchList(value, 1), 300);
});

function goToPage(page: number): void {
    store.fetchList(search.value, page);
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

        <div class="mt-4 surface-shell">
            <table class="w-full text-sm">
                <thead class="border-b border-slate-100 bg-slate-50/60 text-left">
                    <tr>
                        <th class="table-header-cell">Prisoner #</th>
                        <th class="table-header-cell">Name</th>
                        <th class="table-header-cell">Admission</th>
                        <th class="table-header-cell">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="prisoner in store.prisoners"
                        :key="prisoner.id"
                        class="table-row cursor-pointer"
                        @click="$router.push({ name: 'prisoners.show', params: { id: prisoner.id } })"
                    >
                        <td class="px-4 py-3 font-medium text-slate-900">{{ prisoner.prisoner_number }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ prisoner.full_name }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ prisoner.admission_date }}</td>
                        <td class="px-4 py-3"><StatusBadge :status="prisoner.status" /></td>
                    </tr>
                    <tr v-if="!store.loading && store.prisoners.length === 0">
                        <td colspan="4" class="px-4 py-6 text-center text-slate-500">No prisoners found.</td>
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

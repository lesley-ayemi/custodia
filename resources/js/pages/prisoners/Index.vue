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
            <h1 class="text-xl font-semibold text-slate-900">Prisoners</h1>
            <router-link
                v-if="auth.hasRole('officer', 'admin')"
                :to="{ name: 'prisoners.create' }"
                class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800"
            >
                Register prisoner
            </router-link>
        </div>

        <input
            v-model="search"
            type="search"
            placeholder="Search prisoners…"
            class="mt-4 block w-full max-w-sm rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none"
        />

        <div class="mt-4 overflow-hidden rounded-lg border border-slate-200 bg-white">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-medium tracking-wider text-slate-500 uppercase">
                    <tr>
                        <th class="px-4 py-3">Prisoner #</th>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Admission</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr
                        v-for="prisoner in store.prisoners"
                        :key="prisoner.id"
                        class="cursor-pointer hover:bg-slate-50"
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
                    class="rounded-md border border-slate-300 px-3 py-1 disabled:opacity-40"
                    @click="goToPage(store.currentPage - 1)"
                >
                    Previous
                </button>
                <button
                    type="button"
                    :disabled="store.currentPage >= store.lastPage"
                    class="rounded-md border border-slate-300 px-3 py-1 disabled:opacity-40"
                    @click="goToPage(store.currentPage + 1)"
                >
                    Next
                </button>
            </div>
        </div>
    </DashboardLayout>
</template>

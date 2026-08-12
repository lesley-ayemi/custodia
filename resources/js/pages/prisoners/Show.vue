<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import DashboardLayout from '../../layouts/DashboardLayout.vue';
import StatusBadge from '../../components/StatusBadge.vue';
import HousingHistoryTimeline from '../../components/HousingHistoryTimeline.vue';
import CourtCasesPanel from '../../components/CourtCasesPanel.vue';
import PropertyPanel from '../../components/PropertyPanel.vue';
import ProgrammesPanel from '../../components/ProgrammesPanel.vue';
import ReleaseReviewPanel from '../../components/ReleaseReviewPanel.vue';
import MedicalAlertsPanel from '../../components/MedicalAlertsPanel.vue';
import MedicalPanel from '../../components/MedicalPanel.vue';
import { usePrisonerStore } from '../../stores/prisoner';
import { useHousingStore } from '../../stores/housing';
import { useAuthStore } from '../../stores/auth';
import type { Prisoner } from '../../types/prisoner';
import type { HousingAssignment } from '../../types/housing';

const route = useRoute();
const router = useRouter();
const prisonerStore = usePrisonerStore();
const housingStore = useHousingStore();
const auth = useAuthStore();

const prisoner = ref<Prisoner | null>(null);
const history = ref<HousingAssignment[]>([]);
const archiving = ref(false);
const assigning = ref(false);
const selectedCellId = ref<number | null>(null);

const availableCells = computed(() =>
    housingStore.blocks.flatMap((block) => block.cells.map((cell) => ({ ...cell, blockName: block.name }))).filter((cell) => cell.available > 0),
);

const canSeeCustodyOperations = computed(() => auth.hasRole('admin', 'officer', 'supervisor'));

function formatDate(value: string | null): string {
    if (!value) return '—';
    return new Date(value).toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });
}

async function load(): Promise<void> {
    const id = Number(route.params.id);
    prisoner.value = await prisonerStore.fetchOne(id);

    if (canSeeCustodyOperations.value) {
        history.value = await housingStore.fetchHistory(id);
        await housingStore.fetchBlocks();
    }
}

async function archive(): Promise<void> {
    if (!prisoner.value) return;
    archiving.value = true;

    try {
        await prisonerStore.archive(prisoner.value.id);
        await router.push({ name: 'prisoners.index' });
    } finally {
        archiving.value = false;
    }
}

async function assignCell(): Promise<void> {
    if (!prisoner.value || !selectedCellId.value) return;
    assigning.value = true;

    try {
        await housingStore.assign(prisoner.value.id, selectedCellId.value);
        selectedCellId.value = null;
        await load();
    } finally {
        assigning.value = false;
    }
}

onMounted(load);
</script>

<template>
    <DashboardLayout>
        <div v-if="prisoner">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">{{ prisoner.prisoner_number }}</p>
                    <h1 class="text-2xl font-semibold text-slate-900">{{ prisoner.full_name }}</h1>
                </div>
                <StatusBadge :status="prisoner.status" />
            </div>

            <div class="mt-6 grid max-w-2xl grid-cols-2 gap-x-6 gap-y-4 rounded-lg border border-slate-200 bg-white p-6 text-sm">
                <div>
                    <dt class="text-slate-500">Date of birth</dt>
                    <dd class="mt-1 font-medium text-slate-900">{{ formatDate(prisoner.date_of_birth) }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Gender</dt>
                    <dd class="mt-1 font-medium text-slate-900 capitalize">{{ prisoner.gender }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Admission date</dt>
                    <dd class="mt-1 font-medium text-slate-900">{{ formatDate(prisoner.admission_date) }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Expected release</dt>
                    <dd class="mt-1 font-medium text-slate-900">{{ formatDate(prisoner.expected_release_date) }}</dd>
                </div>
            </div>

            <div class="mt-6 max-w-4xl">
                <MedicalAlertsPanel :prisoner-id="prisoner.id" />
            </div>

            <div v-if="canSeeCustodyOperations" class="mt-6 grid max-w-4xl grid-cols-2 gap-6">
                <div class="rounded-lg border border-slate-200 bg-white p-6">
                    <h2 class="text-sm font-semibold text-slate-700">Current cell</h2>
                    <p v-if="prisoner.current_cell" class="mt-2 text-lg font-medium text-slate-900">
                        {{ prisoner.current_cell.block_name }} / {{ prisoner.current_cell.cell_code }}
                    </p>
                    <p v-else class="mt-2 text-sm text-slate-500">Not currently housed.</p>

                    <div v-if="auth.hasRole('officer', 'admin')" class="mt-4 flex items-center gap-2">
                        <select v-model="selectedCellId" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                            <option :value="null">Select a cell…</option>
                            <option v-for="cell in availableCells" :key="cell.id" :value="cell.id">
                                {{ cell.blockName }} / {{ cell.code }} ({{ cell.available }} free)
                            </option>
                        </select>
                        <button
                            type="button"
                            :disabled="!selectedCellId || assigning"
                            class="shrink-0 rounded-md bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-800 disabled:opacity-50"
                            @click="assignCell"
                        >
                            {{ assigning ? 'Assigning…' : 'Assign' }}
                        </button>
                    </div>
                </div>

                <div class="rounded-lg border border-slate-200 bg-white p-6">
                    <h2 class="text-sm font-semibold text-slate-700">Housing history</h2>
                    <div class="mt-4">
                        <HousingHistoryTimeline :history="history" />
                    </div>
                </div>
            </div>

            <div v-if="canSeeCustodyOperations" class="mt-6 max-w-4xl">
                <CourtCasesPanel :prisoner-id="prisoner.id" />
            </div>

            <div v-if="canSeeCustodyOperations" class="mt-6 max-w-4xl">
                <PropertyPanel :prisoner-id="prisoner.id" />
            </div>

            <div v-if="canSeeCustodyOperations" class="mt-6 max-w-4xl">
                <ProgrammesPanel :prisoner-id="prisoner.id" />
            </div>

            <div v-if="canSeeCustodyOperations" class="mt-6 max-w-4xl">
                <ReleaseReviewPanel :prisoner-id="prisoner.id" :prisoner-status="prisoner.status" @released="load" />
            </div>

            <div v-if="auth.hasRole('medical', 'admin')" class="mt-6 max-w-4xl">
                <MedicalPanel :prisoner-id="prisoner.id" />
            </div>

            <div v-if="auth.hasRole('officer', 'admin')" class="mt-6">
                <button
                    type="button"
                    :disabled="archiving"
                    class="rounded-md border border-red-200 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50 disabled:opacity-50"
                    @click="archive"
                >
                    {{ archiving ? 'Archiving…' : 'Archive prisoner' }}
                </button>
            </div>
        </div>
    </DashboardLayout>
</template>

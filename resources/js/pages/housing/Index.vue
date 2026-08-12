<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import DashboardLayout from '../../layouts/DashboardLayout.vue';
import OccupancyBar from '../../components/OccupancyBar.vue';
import { useHousingStore } from '../../stores/housing';
import { useAuthStore } from '../../stores/auth';

const store = useHousingStore();
const auth = useAuthStore();

const error = ref<string | null>(null);
const busy = ref(false);

const showBlockForm = ref(false);
const newBlockName = ref('');

const editingBlockId = ref<number | null>(null);
const editBlockName = ref('');

const wingFormOpenFor = ref<number | null>(null);
const newWingName = ref('');

const editingWingId = ref<number | null>(null);
const editWingName = ref('');

const cellFormOpenFor = ref<number | null>(null);
const newCell = reactive({ code: '', capacity: 2 });

const editingCellId = ref<number | null>(null);
const editCell = reactive({ code: '', capacity: 2 });

function extractError(err: unknown): string {
    const response = (err as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } }).response;
    const errors = response?.data?.errors;
    if (errors) return Object.values(errors).flat().join(' ');
    return response?.data?.message ?? 'Something went wrong.';
}

async function load(): Promise<void> {
    await store.fetchBlocks();
}

async function submitNewBlock(): Promise<void> {
    error.value = null;
    busy.value = true;

    try {
        await store.createBlock(newBlockName.value);
        newBlockName.value = '';
        showBlockForm.value = false;
        await load();
    } catch (err) {
        error.value = extractError(err);
    } finally {
        busy.value = false;
    }
}

function startEditBlock(id: number, name: string): void {
    editingBlockId.value = id;
    editBlockName.value = name;
}

async function saveBlockName(id: number): Promise<void> {
    error.value = null;
    busy.value = true;

    try {
        await store.renameBlock(id, editBlockName.value);
        editingBlockId.value = null;
        await load();
    } catch (err) {
        error.value = extractError(err);
    } finally {
        busy.value = false;
    }
}

async function removeBlock(id: number): Promise<void> {
    error.value = null;
    busy.value = true;

    try {
        await store.deleteBlock(id);
        await load();
    } catch (err) {
        error.value = extractError(err);
    } finally {
        busy.value = false;
    }
}

function toggleWingForm(blockId: number): void {
    wingFormOpenFor.value = wingFormOpenFor.value === blockId ? null : blockId;
    newWingName.value = '';
}

async function submitNewWing(blockId: number): Promise<void> {
    error.value = null;
    busy.value = true;

    try {
        await store.createWing(blockId, newWingName.value);
        wingFormOpenFor.value = null;
        await load();
    } catch (err) {
        error.value = extractError(err);
    } finally {
        busy.value = false;
    }
}

function startEditWing(id: number, name: string): void {
    editingWingId.value = id;
    editWingName.value = name;
}

async function saveWingName(id: number): Promise<void> {
    error.value = null;
    busy.value = true;

    try {
        await store.renameWing(id, editWingName.value);
        editingWingId.value = null;
        await load();
    } catch (err) {
        error.value = extractError(err);
    } finally {
        busy.value = false;
    }
}

async function removeWing(id: number): Promise<void> {
    error.value = null;
    busy.value = true;

    try {
        await store.deleteWing(id);
        await load();
    } catch (err) {
        error.value = extractError(err);
    } finally {
        busy.value = false;
    }
}

function toggleCellForm(wingId: number): void {
    cellFormOpenFor.value = cellFormOpenFor.value === wingId ? null : wingId;
    newCell.code = '';
    newCell.capacity = 2;
}

async function submitNewCell(wingId: number): Promise<void> {
    error.value = null;
    busy.value = true;

    try {
        await store.createCell(wingId, newCell.code, newCell.capacity);
        cellFormOpenFor.value = null;
        await load();
    } catch (err) {
        error.value = extractError(err);
    } finally {
        busy.value = false;
    }
}

function startEditCell(id: number, code: string, capacity: number): void {
    editingCellId.value = id;
    editCell.code = code;
    editCell.capacity = capacity;
}

async function saveCell(id: number): Promise<void> {
    error.value = null;
    busy.value = true;

    try {
        await store.updateCell(id, { code: editCell.code, capacity: editCell.capacity });
        editingCellId.value = null;
        await load();
    } catch (err) {
        error.value = extractError(err);
    } finally {
        busy.value = false;
    }
}

async function removeCell(id: number): Promise<void> {
    error.value = null;
    busy.value = true;

    try {
        await store.deleteCell(id);
        await load();
    } catch (err) {
        error.value = extractError(err);
    } finally {
        busy.value = false;
    }
}

onMounted(load);
</script>

<template>
    <DashboardLayout>
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-slate-900">Housing</h1>
            <button
                v-if="auth.hasRole('admin')"
                type="button"
                class="btn-primary"
                @click="showBlockForm = !showBlockForm"
            >
                {{ showBlockForm ? 'Cancel' : '+ Add block' }}
            </button>
        </div>

        <p v-if="error" class="mt-3 rounded-xl bg-red-50 px-3.5 py-2.5 text-sm text-red-700">{{ error }}</p>

        <form v-if="showBlockForm" class="mt-4 flex items-end gap-2 rounded-xl border border-slate-100 bg-slate-50/60 p-4" @submit.prevent="submitNewBlock">
            <div class="flex-1">
                <label class="field-label">Block name</label>
                <input
                    v-model="newBlockName"
                    type="text"
                    required
                    placeholder="Block D"
                    class="mt-1 field-input-sm"
                />
            </div>
            <button type="submit" :disabled="busy" class="btn-primary-sm">
                Create
            </button>
        </form>

        <div class="mt-6 space-y-10">
            <div v-for="block in store.blocks" :key="block.id">
                <div class="flex items-center justify-between">
                    <div v-if="editingBlockId === block.id" class="flex items-center gap-2">
                        <input v-model="editBlockName" type="text" class="field-input-sm" />
                        <button type="button" :disabled="busy" class="text-sm font-medium text-emerald-700 hover:underline" @click="saveBlockName(block.id)">
                            Save
                        </button>
                        <button type="button" class="text-sm text-slate-500 hover:underline" @click="editingBlockId = null">Cancel</button>
                    </div>
                    <h2 v-else class="text-sm font-semibold text-slate-700">{{ block.name }}</h2>

                    <div v-if="auth.hasRole('admin') && editingBlockId !== block.id" class="flex items-center gap-3 text-xs">
                        <button type="button" class="text-slate-500 hover:underline" @click="toggleWingForm(block.id)">
                            {{ wingFormOpenFor === block.id ? 'Cancel' : '+ Add wing' }}
                        </button>
                        <button type="button" class="text-slate-500 hover:underline" @click="startEditBlock(block.id, block.name)">Rename</button>
                        <button type="button" class="text-red-600 hover:underline" @click="removeBlock(block.id)">Delete</button>
                    </div>
                </div>

                <form
                    v-if="wingFormOpenFor === block.id"
                    class="mt-3 flex items-end gap-2 rounded-xl border border-slate-100 bg-slate-50/60 p-3"
                    @submit.prevent="submitNewWing(block.id)"
                >
                    <div>
                        <label class="field-label">Wing name</label>
                        <input
                            v-model="newWingName"
                            type="text"
                            required
                            placeholder="Wing 3"
                            class="mt-1 field-input-sm"
                        />
                    </div>
                    <button type="submit" :disabled="busy" class="btn-primary-sm">
                        Create
                    </button>
                </form>

                <div class="mt-4 space-y-6 border-l-2 border-slate-100 pl-4">
                    <div v-for="wing in block.wings" :key="wing.id">
                        <div class="flex items-center justify-between">
                            <div v-if="editingWingId === wing.id" class="flex items-center gap-2">
                                <input v-model="editWingName" type="text" class="field-input-sm text-xs" />
                                <button
                                    type="button"
                                    :disabled="busy"
                                    class="text-xs font-medium text-emerald-700 hover:underline"
                                    @click="saveWingName(wing.id)"
                                >
                                    Save
                                </button>
                                <button type="button" class="text-xs text-slate-500 hover:underline" @click="editingWingId = null">Cancel</button>
                            </div>
                            <h3 v-else class="text-xs font-semibold tracking-wide text-slate-500 uppercase">{{ wing.name }}</h3>

                            <div v-if="auth.hasRole('admin') && editingWingId !== wing.id" class="flex items-center gap-3 text-xs">
                                <button type="button" class="text-slate-500 hover:underline" @click="toggleCellForm(wing.id)">
                                    {{ cellFormOpenFor === wing.id ? 'Cancel' : '+ Add cell' }}
                                </button>
                                <button type="button" class="text-slate-500 hover:underline" @click="startEditWing(wing.id, wing.name)">Rename</button>
                                <button type="button" class="text-red-600 hover:underline" @click="removeWing(wing.id)">Delete</button>
                            </div>
                        </div>

                        <form
                            v-if="cellFormOpenFor === wing.id"
                            class="mt-3 flex items-end gap-2 rounded-xl border border-slate-100 bg-slate-50/60 p-3"
                            @submit.prevent="submitNewCell(wing.id)"
                        >
                            <div>
                                <label class="field-label">Code</label>
                                <input
                                    v-model="newCell.code"
                                    type="text"
                                    required
                                    placeholder="D-101"
                                    class="mt-1 field-input-sm"
                                />
                            </div>
                            <div>
                                <label class="field-label">Capacity</label>
                                <input
                                    v-model.number="newCell.capacity"
                                    type="number"
                                    min="1"
                                    max="20"
                                    required
                                    class="mt-1 w-20 rounded-lg border border-slate-200 px-2 py-1 text-sm focus:border-primary-400 focus:outline-none"
                                />
                            </div>
                            <button type="submit" :disabled="busy" class="btn-primary-sm">
                                Create
                            </button>
                        </form>

                        <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            <div v-for="cell in wing.cells" :key="cell.id" class="rounded-xl border border-slate-100 bg-white p-4 shadow-sm">
                                <template v-if="editingCellId === cell.id">
                                    <div class="space-y-2">
                                        <input v-model="editCell.code" type="text" class="field-input-sm w-full" />
                                        <input
                                            v-model.number="editCell.capacity"
                                            type="number"
                                            min="1"
                                            max="20"
                                            class="field-input-sm w-full"
                                        />
                                        <div class="flex gap-2 text-xs">
                                            <button
                                                type="button"
                                                :disabled="busy"
                                                class="font-medium text-emerald-700 hover:underline"
                                                @click="saveCell(cell.id)"
                                            >
                                                Save
                                            </button>
                                            <button type="button" class="text-slate-500 hover:underline" @click="editingCellId = null">Cancel</button>
                                        </div>
                                    </div>
                                </template>
                                <template v-else>
                                    <div class="flex items-center justify-between">
                                        <span class="font-medium text-slate-900">{{ cell.code }}</span>
                                        <span class="text-xs text-slate-500">{{ cell.occupancy }} / {{ cell.capacity }}</span>
                                    </div>
                                    <OccupancyBar class="mt-3" :occupied="cell.occupancy" :capacity="cell.capacity" />
                                    <div v-if="auth.hasRole('admin')" class="mt-3 flex gap-3 text-xs">
                                        <button type="button" class="text-slate-500 hover:underline" @click="startEditCell(cell.id, cell.code, cell.capacity)">
                                            Edit
                                        </button>
                                        <button type="button" class="text-red-600 hover:underline" @click="removeCell(cell.id)">Delete</button>
                                    </div>
                                </template>
                            </div>
                            <p v-if="wing.cells.length === 0" class="text-sm text-slate-400">No cells in this wing yet.</p>
                        </div>
                    </div>
                    <p v-if="block.wings.length === 0" class="text-sm text-slate-400">No wings in this block yet.</p>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

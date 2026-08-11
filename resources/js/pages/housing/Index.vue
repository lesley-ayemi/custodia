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

function toggleCellForm(blockId: number): void {
    cellFormOpenFor.value = cellFormOpenFor.value === blockId ? null : blockId;
    newCell.code = '';
    newCell.capacity = 2;
}

async function submitNewCell(blockId: number): Promise<void> {
    error.value = null;
    busy.value = true;

    try {
        await store.createCell(blockId, newCell.code, newCell.capacity);
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
            <h1 class="text-xl font-semibold text-slate-900">Housing</h1>
            <button
                v-if="auth.hasRole('admin')"
                type="button"
                class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800"
                @click="showBlockForm = !showBlockForm"
            >
                {{ showBlockForm ? 'Cancel' : '+ Add block' }}
            </button>
        </div>

        <p v-if="error" class="mt-3 text-sm text-red-600">{{ error }}</p>

        <form v-if="showBlockForm" class="mt-4 flex items-end gap-2 rounded-md border border-slate-200 bg-slate-50 p-4" @submit.prevent="submitNewBlock">
            <div class="flex-1">
                <label class="block text-xs font-medium text-slate-600">Block name</label>
                <input
                    v-model="newBlockName"
                    type="text"
                    required
                    placeholder="Block D"
                    class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-1.5 text-sm"
                />
            </div>
            <button type="submit" :disabled="busy" class="rounded-md bg-slate-900 px-3 py-1.5 text-sm font-medium text-white disabled:opacity-50">
                Create
            </button>
        </form>

        <div class="mt-6 space-y-8">
            <div v-for="block in store.blocks" :key="block.id">
                <div class="flex items-center justify-between">
                    <div v-if="editingBlockId === block.id" class="flex items-center gap-2">
                        <input v-model="editBlockName" type="text" class="rounded-md border border-slate-300 px-2 py-1 text-sm" />
                        <button type="button" :disabled="busy" class="text-sm font-medium text-emerald-700 hover:underline" @click="saveBlockName(block.id)">
                            Save
                        </button>
                        <button type="button" class="text-sm text-slate-500 hover:underline" @click="editingBlockId = null">Cancel</button>
                    </div>
                    <h2 v-else class="text-sm font-semibold text-slate-700">{{ block.name }}</h2>

                    <div v-if="auth.hasRole('admin') && editingBlockId !== block.id" class="flex items-center gap-3 text-xs">
                        <button type="button" class="text-slate-500 hover:underline" @click="toggleCellForm(block.id)">
                            {{ cellFormOpenFor === block.id ? 'Cancel' : '+ Add cell' }}
                        </button>
                        <button type="button" class="text-slate-500 hover:underline" @click="startEditBlock(block.id, block.name)">Rename</button>
                        <button type="button" class="text-red-600 hover:underline" @click="removeBlock(block.id)">Delete</button>
                    </div>
                </div>

                <form
                    v-if="cellFormOpenFor === block.id"
                    class="mt-3 flex items-end gap-2 rounded-md border border-slate-200 bg-slate-50 p-3"
                    @submit.prevent="submitNewCell(block.id)"
                >
                    <div>
                        <label class="block text-xs font-medium text-slate-600">Code</label>
                        <input v-model="newCell.code" type="text" required placeholder="D-101" class="mt-1 rounded-md border border-slate-300 px-2 py-1 text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600">Capacity</label>
                        <input
                            v-model.number="newCell.capacity"
                            type="number"
                            min="1"
                            max="20"
                            required
                            class="mt-1 w-20 rounded-md border border-slate-300 px-2 py-1 text-sm"
                        />
                    </div>
                    <button type="submit" :disabled="busy" class="rounded-md bg-slate-900 px-3 py-1.5 text-sm font-medium text-white disabled:opacity-50">
                        Create
                    </button>
                </form>

                <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="cell in block.cells" :key="cell.id" class="rounded-lg border border-slate-200 bg-white p-4">
                        <template v-if="editingCellId === cell.id">
                            <div class="space-y-2">
                                <input v-model="editCell.code" type="text" class="block w-full rounded-md border border-slate-300 px-2 py-1 text-sm" />
                                <input
                                    v-model.number="editCell.capacity"
                                    type="number"
                                    min="1"
                                    max="20"
                                    class="block w-full rounded-md border border-slate-300 px-2 py-1 text-sm"
                                />
                                <div class="flex gap-2 text-xs">
                                    <button type="button" :disabled="busy" class="font-medium text-emerald-700 hover:underline" @click="saveCell(cell.id)">
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
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

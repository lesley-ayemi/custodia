<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue';
import { usePropertyStore } from '../stores/property';
import { useAuthStore } from '../stores/auth';
import type { PropertyItem, PropertyItemDraft } from '../types/property';

const props = defineProps<{ prisonerId: number }>();

const store = usePropertyStore();
const auth = useAuthStore();

const showForm = ref(false);
const submitting = ref(false);
const releasingId = ref<number | null>(null);
const draftItems = reactive<PropertyItemDraft[]>([{ description: '', quantity: 1, storage_location: '' }]);

const bags = computed(() => {
    const grouped = new Map<string, PropertyItem[]>();
    for (const item of store.items) {
        if (!grouped.has(item.property_number)) grouped.set(item.property_number, []);
        grouped.get(item.property_number)!.push(item);
    }
    return Array.from(grouped.entries()).map(([propertyNumber, items]) => ({ propertyNumber, items }));
});

function formatDate(value: string): string {
    return new Date(value).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
}

async function load(): Promise<void> {
    await store.fetchForPrisoner(props.prisonerId);
}

function addRow(): void {
    draftItems.push({ description: '', quantity: 1, storage_location: '' });
}

function removeRow(index: number): void {
    draftItems.splice(index, 1);
}

function resetForm(): void {
    draftItems.splice(0, draftItems.length, { description: '', quantity: 1, storage_location: '' });
}

async function submit(): Promise<void> {
    submitting.value = true;

    try {
        await store.receiveBag(props.prisonerId, draftItems);
        showForm.value = false;
        resetForm();
        await load();
    } finally {
        submitting.value = false;
    }
}

async function releaseItem(id: number): Promise<void> {
    releasingId.value = id;

    try {
        await store.release(id);
        await load();
    } finally {
        releasingId.value = null;
    }
}

onMounted(load);
</script>

<template>
    <div class="rounded-lg border border-slate-200 bg-white p-6">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-700">Property</h2>
            <button
                v-if="auth.hasRole('officer', 'admin')"
                type="button"
                class="text-sm font-medium text-slate-900 hover:underline"
                @click="showForm = !showForm"
            >
                {{ showForm ? 'Cancel' : '+ Receive property' }}
            </button>
        </div>

        <form v-if="showForm" class="mt-4 space-y-3 rounded-md border border-slate-200 bg-slate-50 p-4" @submit.prevent="submit">
            <div v-for="(row, index) in draftItems" :key="index" class="flex items-end gap-2">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-slate-600">Description</label>
                    <input
                        v-model="row.description"
                        type="text"
                        required
                        placeholder="Phone"
                        class="mt-1 block w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm"
                    />
                </div>
                <div class="w-20">
                    <label class="block text-xs font-medium text-slate-600">Qty</label>
                    <input
                        v-model.number="row.quantity"
                        type="number"
                        min="1"
                        required
                        class="mt-1 block w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm"
                    />
                </div>
                <div class="flex-1">
                    <label class="block text-xs font-medium text-slate-600">Storage location</label>
                    <input
                        v-model="row.storage_location"
                        type="text"
                        required
                        placeholder="Store A"
                        class="mt-1 block w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm"
                    />
                </div>
                <button
                    v-if="draftItems.length > 1"
                    type="button"
                    class="mb-1.5 text-xs text-red-600 hover:underline"
                    @click="removeRow(index)"
                >
                    Remove
                </button>
            </div>

            <div class="flex items-center gap-3">
                <button type="button" class="text-sm text-slate-600 hover:underline" @click="addRow">+ Add item</button>
                <button
                    type="submit"
                    :disabled="submitting"
                    class="rounded-md bg-slate-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-slate-800 disabled:opacity-50"
                >
                    {{ submitting ? 'Saving…' : 'Receive bag' }}
                </button>
            </div>
        </form>

        <div class="mt-4 space-y-4">
            <div v-for="bag in bags" :key="bag.propertyNumber" class="rounded-md border border-slate-200 p-4">
                <p class="text-sm font-medium text-slate-900">Property Bag {{ bag.propertyNumber }}</p>
                <ul class="mt-2 space-y-1">
                    <li v-for="item in bag.items" :key="item.id" class="flex items-center justify-between text-sm">
                        <span class="text-slate-600">
                            {{ item.description }} <span v-if="item.quantity > 1">×{{ item.quantity }}</span>
                            <span class="text-xs text-slate-400"> · {{ item.storage_location }}</span>
                        </span>
                        <span v-if="item.released_at" class="text-xs text-slate-400">
                            Released {{ formatDate(item.released_at) }}
                        </span>
                        <button
                            v-else-if="auth.hasRole('officer', 'admin')"
                            type="button"
                            :disabled="releasingId === item.id"
                            class="text-xs font-medium text-emerald-700 hover:underline disabled:opacity-50"
                            @click="releaseItem(item.id)"
                        >
                            Release
                        </button>
                        <span v-else class="text-xs text-emerald-600">Held</span>
                    </li>
                </ul>
            </div>

            <p v-if="!store.loading && bags.length === 0" class="text-sm text-slate-500">No property on file.</p>
        </div>
    </div>
</template>

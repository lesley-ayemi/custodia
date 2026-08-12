<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import DashboardLayout from '../../layouts/DashboardLayout.vue';
import DataTable from '../../components/DataTable.vue';
import type { DataTableColumn } from '../../components/DataTable.vue';
import { useVisitorStore } from '../../stores/visitor';
import { useAuthStore } from '../../stores/auth';
import type { Visitor, VisitorFormData, VisitorIdType } from '../../types/visitor';

const store = useVisitorStore();
const auth = useAuthStore();

const error = ref<string | null>(null);
const busy = ref(false);
const showForm = ref(false);

const idTypes: { value: VisitorIdType; label: string }[] = [
    { value: 'passport', label: 'Passport' },
    { value: 'driving_licence', label: 'Driving licence' },
    { value: 'national_id', label: 'National ID' },
    { value: 'other', label: 'Other' },
];

const columns: DataTableColumn[] = [
    { key: 'name', label: 'Name', sortable: true },
    { key: 'date_of_birth', label: 'Date of birth', sortable: true },
    { key: 'id_number', label: 'ID' },
    { key: 'phone', label: 'Phone' },
    { key: 'status', label: 'Status' },
];

const form = reactive<VisitorFormData>({
    name: '',
    date_of_birth: '',
    id_type: 'passport',
    id_number: '',
    phone: '',
    email: '',
    address: '',
});

function extractError(err: unknown): string {
    const response = (err as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } }).response;
    const errors = response?.data?.errors;
    if (errors) return Object.values(errors).flat().join(' ');
    return response?.data?.message ?? 'Something went wrong.';
}

async function load(): Promise<void> {
    await store.fetchVisitors();
}

async function submit(): Promise<void> {
    error.value = null;
    busy.value = true;

    try {
        await store.registerVisitor(form);
        form.name = '';
        form.date_of_birth = '';
        form.id_type = 'passport';
        form.id_number = '';
        form.phone = '';
        form.email = '';
        form.address = '';
        showForm.value = false;
        await load();
    } catch (err) {
        error.value = extractError(err);
    } finally {
        busy.value = false;
    }
}

function formatDate(value: string): string {
    return new Date(value).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
}

onMounted(load);
</script>

<template>
    <DashboardLayout>
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-slate-900">Visitors</h1>
            <button
                v-if="auth.hasRole('officer', 'admin')"
                type="button"
                class="btn-primary"
                @click="showForm = !showForm"
            >
                {{ showForm ? 'Cancel' : '+ Register visitor' }}
            </button>
        </div>

        <p v-if="error" class="mt-3 rounded-xl bg-red-50 px-3.5 py-2.5 text-sm text-red-700">{{ error }}</p>

        <form v-if="showForm" class="mt-4 space-y-3 rounded-xl border border-slate-100 bg-slate-50/60 p-4" @submit.prevent="submit">
            <div class="grid grid-cols-2 gap-3">
                <input v-model="form.name" type="text" required placeholder="Full name" class="field-input-sm" />
                <input v-model="form.date_of_birth" type="date" required class="field-input-sm" />
            </div>
            <div class="grid grid-cols-2 gap-3">
                <select v-model="form.id_type" class="field-input-sm">
                    <option v-for="t in idTypes" :key="t.value" :value="t.value">{{ t.label }}</option>
                </select>
                <input v-model="form.id_number" type="text" required placeholder="ID number" class="field-input-sm" />
            </div>
            <div class="grid grid-cols-2 gap-3">
                <input v-model="form.phone" type="text" required placeholder="Phone" class="field-input-sm" />
                <input v-model="form.email" type="email" placeholder="Email (optional)" class="field-input-sm" />
            </div>
            <input v-model="form.address" type="text" placeholder="Address (optional)" class="field-input-sm" />
            <button type="submit" :disabled="busy" class="btn-primary-sm">
                Register
            </button>
        </form>

        <div class="mt-6">
            <DataTable
                :columns="columns"
                :rows="store.visitors as unknown as Record<string, unknown>[]"
                :loading="store.loading"
                empty-message="No visitors registered."
                searchable
                search-placeholder="Search visitors…"
            >
                <template #cell-name="{ value }">
                    <span class="font-medium text-slate-900">{{ value }}</span>
                </template>
                <template #cell-date_of_birth="{ value }">
                    <span class="text-slate-500">{{ formatDate(value as string) }}</span>
                </template>
                <template #cell-id_number="{ value }">
                    <span class="text-slate-500">{{ value }}</span>
                </template>
                <template #cell-phone="{ value }">
                    <span class="text-slate-500">{{ value }}</span>
                </template>
                <template #cell-status="{ row }">
                    <span
                        v-if="(row as unknown as Visitor).banned_at"
                        class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800"
                    >
                        Banned
                    </span>
                    <span v-else class="text-xs text-slate-400">—</span>
                </template>
            </DataTable>
        </div>
    </DashboardLayout>
</template>

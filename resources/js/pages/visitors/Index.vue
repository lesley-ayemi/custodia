<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import DashboardLayout from '../../layouts/DashboardLayout.vue';
import { useVisitorStore } from '../../stores/visitor';
import { useAuthStore } from '../../stores/auth';
import type { VisitorFormData, VisitorIdType } from '../../types/visitor';

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
            <h1 class="text-xl font-semibold text-slate-900">Visitors</h1>
            <button
                v-if="auth.hasRole('officer', 'admin')"
                type="button"
                class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800"
                @click="showForm = !showForm"
            >
                {{ showForm ? 'Cancel' : '+ Register visitor' }}
            </button>
        </div>

        <p v-if="error" class="mt-3 text-sm text-red-600">{{ error }}</p>

        <form v-if="showForm" class="mt-4 space-y-3 rounded-md border border-slate-200 bg-slate-50 p-4" @submit.prevent="submit">
            <div class="grid grid-cols-2 gap-3">
                <input v-model="form.name" type="text" required placeholder="Full name" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm" />
                <input v-model="form.date_of_birth" type="date" required class="rounded-md border border-slate-300 px-3 py-1.5 text-sm" />
            </div>
            <div class="grid grid-cols-2 gap-3">
                <select v-model="form.id_type" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
                    <option v-for="t in idTypes" :key="t.value" :value="t.value">{{ t.label }}</option>
                </select>
                <input v-model="form.id_number" type="text" required placeholder="ID number" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm" />
            </div>
            <div class="grid grid-cols-2 gap-3">
                <input v-model="form.phone" type="text" required placeholder="Phone" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm" />
                <input v-model="form.email" type="email" placeholder="Email (optional)" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm" />
            </div>
            <input v-model="form.address" type="text" placeholder="Address (optional)" class="block w-full rounded-md border border-slate-300 px-3 py-1.5 text-sm" />
            <button type="submit" :disabled="busy" class="rounded-md bg-slate-900 px-3 py-1.5 text-sm font-medium text-white disabled:opacity-50">
                Register
            </button>
        </form>

        <div class="mt-6 overflow-hidden rounded-lg border border-slate-200 bg-white">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-medium tracking-wider text-slate-500 uppercase">
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Date of birth</th>
                        <th class="px-4 py-3">ID</th>
                        <th class="px-4 py-3">Phone</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="visitor in store.visitors" :key="visitor.id">
                        <td class="px-4 py-3 font-medium text-slate-900">{{ visitor.name }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ formatDate(visitor.date_of_birth) }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ visitor.id_number }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ visitor.phone }}</td>
                        <td class="px-4 py-3">
                            <span v-if="visitor.banned_at" class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">
                                Banned
                            </span>
                            <span v-else class="text-xs text-slate-400">—</span>
                        </td>
                    </tr>
                    <tr v-if="!store.loading && store.visitors.length === 0">
                        <td colspan="5" class="px-4 py-6 text-center text-slate-500">No visitors registered.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </DashboardLayout>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import StatusBadge from './StatusBadge.vue';
import { useMovementStore } from '../stores/movement';
import { useAuthStore } from '../stores/auth';
import type { MovementFormData } from '../types/movement';

const props = defineProps<{ prisonerId: number }>();

const store = useMovementStore();
const auth = useAuthStore();

const showForm = ref(false);
const submitting = ref(false);
const form = reactive<MovementFormData>({ from_location: '', to_location: '', reason: '', scheduled_at: '' });
const busyKey = ref<string | null>(null);

function formatDateTime(value: string): string {
    return new Date(value).toLocaleString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

async function load(): Promise<void> {
    await store.fetchForPrisoner(props.prisonerId);
}

async function submit(): Promise<void> {
    submitting.value = true;

    try {
        await store.request(props.prisonerId, form);
        form.from_location = '';
        form.to_location = '';
        form.reason = '';
        form.scheduled_at = '';
        showForm.value = false;
        await load();
    } finally {
        submitting.value = false;
    }
}

async function act(action: 'approve' | 'depart' | 'arrive' | 'markReturned' | 'cancel', id: number): Promise<void> {
    busyKey.value = `${action}-${id}`;

    try {
        await store[action](id);
        await load();
    } finally {
        busyKey.value = null;
    }
}

onMounted(load);
</script>

<template>
    <div class="surface-card">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-700">Movements</h2>
            <button
                v-if="auth.hasRole('officer', 'admin')"
                type="button"
                class="text-sm font-medium text-primary-600 hover:underline"
                @click="showForm = !showForm"
            >
                {{ showForm ? 'Cancel' : '+ Request movement' }}
            </button>
        </div>

        <form v-if="showForm" class="mt-4 space-y-2 rounded-xl border border-slate-100 bg-slate-50/60 p-4" @submit.prevent="submit">
            <div class="grid grid-cols-2 gap-2">
                <input
                    v-model="form.from_location"
                    type="text"
                    required
                    placeholder="From (e.g. HMP Custodia)"
                    class="field-input-sm"
                />
                <input
                    v-model="form.to_location"
                    type="text"
                    required
                    placeholder="To (e.g. Crown Court)"
                    class="field-input-sm"
                />
            </div>
            <div class="grid grid-cols-2 gap-2">
                <input
                    v-model="form.reason"
                    type="text"
                    required
                    placeholder="Reason"
                    class="field-input-sm"
                />
                <input v-model="form.scheduled_at" type="datetime-local" required class="field-input-sm" />
            </div>
            <button
                type="submit"
                :disabled="submitting"
                class="btn-primary-sm"
            >
                {{ submitting ? 'Requesting…' : 'Request movement' }}
            </button>
        </form>

        <ul class="mt-4 space-y-3">
            <li v-for="movement in store.movements" :key="movement.id" class="rounded-xl border border-slate-100 bg-white p-3 text-sm shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="font-medium text-slate-900">{{ movement.from_location }} → {{ movement.to_location }}</p>
                        <p class="text-xs text-slate-500">{{ movement.reason }} · Scheduled {{ formatDateTime(movement.scheduled_at) }}</p>
                    </div>
                    <StatusBadge :status="movement.status" />
                </div>

                <div class="mt-2 flex gap-3 text-xs">
                    <button
                        v-if="movement.status === 'requested' && auth.hasRole('supervisor', 'admin')"
                        type="button"
                        :disabled="busyKey === `approve-${movement.id}`"
                        class="font-medium text-emerald-700 hover:underline disabled:opacity-50"
                        @click="act('approve', movement.id)"
                    >
                        Approve
                    </button>
                    <button
                        v-if="movement.status === 'approved' && auth.hasRole('officer', 'admin')"
                        type="button"
                        :disabled="busyKey === `depart-${movement.id}`"
                        class="font-medium text-emerald-700 hover:underline disabled:opacity-50"
                        @click="act('depart', movement.id)"
                    >
                        Mark departed
                    </button>
                    <button
                        v-if="movement.status === 'departed' && auth.hasRole('officer', 'admin')"
                        type="button"
                        :disabled="busyKey === `arrive-${movement.id}`"
                        class="font-medium text-emerald-700 hover:underline disabled:opacity-50"
                        @click="act('arrive', movement.id)"
                    >
                        Mark arrived
                    </button>
                    <button
                        v-if="movement.status === 'arrived' && auth.hasRole('officer', 'admin')"
                        type="button"
                        :disabled="busyKey === `markReturned-${movement.id}`"
                        class="font-medium text-emerald-700 hover:underline disabled:opacity-50"
                        @click="act('markReturned', movement.id)"
                    >
                        Mark returned
                    </button>
                    <button
                        v-if="['requested', 'approved'].includes(movement.status) && auth.hasRole('officer', 'admin')"
                        type="button"
                        :disabled="busyKey === `cancel-${movement.id}`"
                        class="font-medium text-red-600 hover:underline disabled:opacity-50"
                        @click="act('cancel', movement.id)"
                    >
                        Cancel
                    </button>
                </div>

                <p class="mt-2 text-xs text-slate-400">
                    Requested by {{ movement.requested_by }}
                    <span v-if="movement.approved_by"> · Approved by {{ movement.approved_by }}</span>
                </p>
            </li>
            <p v-if="!store.loading && store.movements.length === 0" class="text-sm text-slate-500">No movements on file.</p>
        </ul>
    </div>
</template>

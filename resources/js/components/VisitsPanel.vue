<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import StatusBadge from './StatusBadge.vue';
import { useVisitorStore } from '../stores/visitor';
import { useAuthStore } from '../stores/auth';

const props = defineProps<{ prisonerId: number }>();

const store = useVisitorStore();
const auth = useAuthStore();

const showForm = ref(false);
const submitting = ref(false);
const form = reactive({ visitor_id: null as number | null, relationship: '', requested_visit_date: '' });

const approveFormOpenFor = ref<number | null>(null);
const scheduledAtDraft = ref('');
const rejectFormOpenFor = ref<number | null>(null);
const rejectReasonDraft = ref('');
const busyKey = ref<string | null>(null);

function formatDate(value: string): string {
    return new Date(value).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
}

function formatDateTime(value: string): string {
    return new Date(value).toLocaleString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

async function load(): Promise<void> {
    await store.fetchRequestsForPrisoner(props.prisonerId);
    await store.fetchVisitors();
}

async function submitRequest(): Promise<void> {
    if (!form.visitor_id) return;
    submitting.value = true;

    try {
        await store.requestVisit({
            visitor_id: form.visitor_id,
            prisoner_id: props.prisonerId,
            relationship: form.relationship,
            requested_visit_date: form.requested_visit_date,
        });
        form.visitor_id = null;
        form.relationship = '';
        form.requested_visit_date = '';
        showForm.value = false;
        await load();
    } finally {
        submitting.value = false;
    }
}

function toggleApproveForm(id: number): void {
    approveFormOpenFor.value = approveFormOpenFor.value === id ? null : id;
    scheduledAtDraft.value = '';
}

async function submitApprove(id: number): Promise<void> {
    busyKey.value = `approve-${id}`;

    try {
        await store.approveRequest(id, scheduledAtDraft.value);
        approveFormOpenFor.value = null;
        await load();
    } finally {
        busyKey.value = null;
    }
}

function toggleRejectForm(id: number): void {
    rejectFormOpenFor.value = rejectFormOpenFor.value === id ? null : id;
    rejectReasonDraft.value = '';
}

async function submitReject(id: number): Promise<void> {
    busyKey.value = `reject-${id}`;

    try {
        await store.rejectRequest(id, rejectReasonDraft.value || null);
        rejectFormOpenFor.value = null;
        await load();
    } finally {
        busyKey.value = null;
    }
}

async function checkIn(visitId: number): Promise<void> {
    busyKey.value = `checkin-${visitId}`;

    try {
        await store.checkIn(visitId);
        await load();
    } finally {
        busyKey.value = null;
    }
}

async function checkOut(visitId: number): Promise<void> {
    busyKey.value = `checkout-${visitId}`;

    try {
        await store.checkOut(visitId, null);
        await load();
    } finally {
        busyKey.value = null;
    }
}

async function cancelVisit(visitId: number): Promise<void> {
    busyKey.value = `cancel-${visitId}`;

    try {
        await store.cancelVisit(visitId);
        await load();
    } finally {
        busyKey.value = null;
    }
}

onMounted(load);
</script>

<template>
    <div class="rounded-lg border border-slate-200 bg-white p-6">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-700">Visits</h2>
            <button
                v-if="auth.hasRole('officer', 'admin')"
                type="button"
                class="text-sm font-medium text-slate-900 hover:underline"
                @click="showForm = !showForm"
            >
                {{ showForm ? 'Cancel' : '+ Request visit' }}
            </button>
        </div>

        <form v-if="showForm" class="mt-4 space-y-2 rounded-md border border-slate-200 bg-slate-50 p-4" @submit.prevent="submitRequest">
            <div class="grid grid-cols-2 gap-2">
                <select v-model="form.visitor_id" required class="rounded-md border border-slate-300 px-2 py-1.5 text-sm">
                    <option :value="null" disabled>Select a visitor…</option>
                    <option v-for="visitor in store.visitors" :key="visitor.id" :value="visitor.id">
                        {{ visitor.name }}<span v-if="visitor.banned_at"> (banned)</span>
                    </option>
                </select>
                <input
                    v-model="form.relationship"
                    type="text"
                    required
                    placeholder="Relationship (e.g. Spouse)"
                    class="rounded-md border border-slate-300 px-2 py-1.5 text-sm"
                />
            </div>
            <input
                v-model="form.requested_visit_date"
                type="date"
                required
                class="block w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm"
            />
            <button
                type="submit"
                :disabled="submitting"
                class="rounded-md bg-slate-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-slate-800 disabled:opacity-50"
            >
                {{ submitting ? 'Requesting…' : 'Request visit' }}
            </button>
        </form>

        <ul class="mt-4 space-y-3">
            <li v-for="request in store.requests" :key="request.id" class="rounded-md border border-slate-200 p-3 text-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="font-medium text-slate-900">{{ request.visitor_name }}</p>
                        <p class="text-xs text-slate-500">{{ request.relationship }} · Requested for {{ formatDate(request.requested_visit_date) }}</p>
                    </div>
                    <StatusBadge :status="request.status" />
                </div>
                <p v-if="request.status === 'rejected' && request.rejection_reason" class="mt-1 text-xs text-slate-500">
                    Reason: {{ request.rejection_reason }}
                </p>

                <div v-if="request.status === 'pending' && auth.hasRole('supervisor', 'admin')" class="mt-2 flex gap-3 text-xs">
                    <button type="button" class="font-medium text-emerald-700 hover:underline" @click="toggleApproveForm(request.id)">
                        {{ approveFormOpenFor === request.id ? 'Cancel' : 'Approve' }}
                    </button>
                    <button type="button" class="font-medium text-red-600 hover:underline" @click="toggleRejectForm(request.id)">
                        {{ rejectFormOpenFor === request.id ? 'Cancel' : 'Reject' }}
                    </button>
                </div>

                <form
                    v-if="approveFormOpenFor === request.id"
                    class="mt-2 flex items-end gap-2 rounded-md bg-slate-50 p-2"
                    @submit.prevent="submitApprove(request.id)"
                >
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-slate-600">Scheduled date/time</label>
                        <input
                            v-model="scheduledAtDraft"
                            type="datetime-local"
                            required
                            class="mt-1 block w-full rounded-md border border-slate-300 px-2 py-1 text-xs"
                        />
                    </div>
                    <button
                        type="submit"
                        :disabled="busyKey === `approve-${request.id}`"
                        class="rounded-md bg-slate-900 px-3 py-1 text-xs font-medium text-white disabled:opacity-50"
                    >
                        {{ busyKey === `approve-${request.id}` ? 'Saving…' : 'Confirm' }}
                    </button>
                </form>

                <form
                    v-if="rejectFormOpenFor === request.id"
                    class="mt-2 flex items-end gap-2 rounded-md bg-slate-50 p-2"
                    @submit.prevent="submitReject(request.id)"
                >
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-slate-600">Reason (optional)</label>
                        <input v-model="rejectReasonDraft" type="text" class="mt-1 block w-full rounded-md border border-slate-300 px-2 py-1 text-xs" />
                    </div>
                    <button
                        type="submit"
                        :disabled="busyKey === `reject-${request.id}`"
                        class="rounded-md bg-red-600 px-3 py-1 text-xs font-medium text-white hover:bg-red-700 disabled:opacity-50"
                    >
                        {{ busyKey === `reject-${request.id}` ? 'Saving…' : 'Confirm reject' }}
                    </button>
                </form>

                <div v-if="request.visit" class="mt-2 flex items-center justify-between border-t border-slate-100 pt-2">
                    <p class="text-xs text-slate-500">
                        Visit {{ formatDateTime(request.visit.scheduled_at) }}
                        <span v-if="request.visit.checked_in_at"> · Checked in {{ formatDateTime(request.visit.checked_in_at) }}</span>
                        <span v-if="request.visit.checked_out_at"> · Checked out {{ formatDateTime(request.visit.checked_out_at) }}</span>
                    </p>
                    <div v-if="auth.hasRole('officer', 'admin')" class="flex gap-3 text-xs">
                        <button
                            v-if="request.visit.status === 'scheduled'"
                            type="button"
                            :disabled="busyKey === `checkin-${request.visit.id}`"
                            class="font-medium text-emerald-700 hover:underline disabled:opacity-50"
                            @click="checkIn(request.visit.id)"
                        >
                            Check in
                        </button>
                        <button
                            v-if="request.visit.status === 'checked_in'"
                            type="button"
                            :disabled="busyKey === `checkout-${request.visit.id}`"
                            class="font-medium text-emerald-700 hover:underline disabled:opacity-50"
                            @click="checkOut(request.visit.id)"
                        >
                            Check out
                        </button>
                        <button
                            v-if="request.visit.status === 'scheduled'"
                            type="button"
                            :disabled="busyKey === `cancel-${request.visit.id}`"
                            class="font-medium text-red-600 hover:underline disabled:opacity-50"
                            @click="cancelVisit(request.visit.id)"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </li>
            <p v-if="!store.loading && store.requests.length === 0" class="text-sm text-slate-500">No visit requests on file.</p>
        </ul>
    </div>
</template>

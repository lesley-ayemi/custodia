<script setup lang="ts">
import { computed, ref } from 'vue';
import { ChevronUp, ChevronDown, ChevronsUpDown, Search } from '@lucide/vue';

export interface DataTableColumn {
    key: string;
    label: string;
    sortable?: boolean;
    align?: 'left' | 'right';
}

const props = withDefaults(
    defineProps<{
        columns: DataTableColumn[];
        rows: Record<string, unknown>[];
        rowKey?: string;
        loading?: boolean;
        emptyMessage?: string;
        searchable?: boolean;
        searchPlaceholder?: string;
        clickableRows?: boolean;
        serverSort?: boolean;
    }>(),
    {
        rowKey: 'id',
        loading: false,
        emptyMessage: 'No records found.',
        searchable: false,
        searchPlaceholder: 'Search…',
        clickableRows: false,
        serverSort: false,
    },
);

const emit = defineEmits<{ rowClick: [row: Record<string, unknown>]; sort: [{ key: string; direction: 'asc' | 'desc' }] }>();

const query = ref('');
const sortKey = ref<string | null>(null);
const sortDir = ref<'asc' | 'desc'>('asc');

function toggleSort(column: DataTableColumn): void {
    if (!column.sortable) return;

    if (sortKey.value !== column.key) {
        sortKey.value = column.key;
        sortDir.value = 'asc';
    } else if (sortDir.value === 'asc') {
        sortDir.value = 'desc';
    } else if (props.serverSort) {
        sortDir.value = 'asc';
    } else {
        sortKey.value = null;
    }

    if (props.serverSort && sortKey.value) {
        emit('sort', { key: sortKey.value, direction: sortDir.value });
    }
}

const filteredRows = computed(() => {
    if (!props.searchable || !query.value.trim()) return props.rows;

    const needle = query.value.trim().toLowerCase();

    return props.rows.filter((row) =>
        props.columns.some((column) => String(row[column.key] ?? '').toLowerCase().includes(needle)),
    );
});

const displayRows = computed(() => {
    if (props.serverSort || !sortKey.value) return filteredRows.value;

    const key = sortKey.value;
    const dir = sortDir.value === 'asc' ? 1 : -1;

    return [...filteredRows.value].sort((a, b) => {
        const av = a[key];
        const bv = b[key];
        if (av == null && bv == null) return 0;
        if (av == null) return -1 * dir;
        if (bv == null) return 1 * dir;
        if (av < bv) return -1 * dir;
        if (av > bv) return 1 * dir;
        return 0;
    });
});
</script>

<template>
    <div>
        <div v-if="searchable" class="mb-4">
            <div class="relative max-w-xs">
                <Search :size="16" class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-slate-400" />
                <input v-model="query" type="search" :placeholder="searchPlaceholder" class="field-input pl-9" />
            </div>
        </div>

        <div class="surface-shell">
            <table class="responsive-table w-full text-sm">
                <thead class="border-b border-slate-100 bg-slate-50/60 text-left">
                    <tr>
                        <th
                            v-for="column in columns"
                            :key="column.key"
                            class="table-header-cell"
                            :class="[column.align === 'right' ? 'text-right' : '', column.sortable ? 'cursor-pointer select-none hover:text-slate-700' : '']"
                            @click="toggleSort(column)"
                        >
                            <span class="inline-flex items-center gap-1" :class="column.align === 'right' ? 'flex-row-reverse' : ''">
                                {{ column.label }}
                                <template v-if="column.sortable">
                                    <ChevronUp v-if="sortKey === column.key && sortDir === 'asc'" :size="12" />
                                    <ChevronDown v-else-if="sortKey === column.key && sortDir === 'desc'" :size="12" />
                                    <ChevronsUpDown v-else :size="12" class="text-slate-300" />
                                </template>
                            </span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="row in displayRows"
                        :key="String(row[rowKey])"
                        class="table-row"
                        :class="clickableRows ? 'cursor-pointer' : ''"
                        @click="clickableRows && emit('rowClick', row)"
                    >
                        <td
                            v-for="column in columns"
                            :key="column.key"
                            :data-label="column.label"
                            class="px-4 py-3"
                            :class="column.align === 'right' ? 'text-right' : ''"
                        >
                            <slot :name="`cell-${column.key}`" :row="row" :value="row[column.key]">
                                {{ row[column.key] }}
                            </slot>
                        </td>
                    </tr>
                    <tr v-if="!loading && displayRows.length === 0">
                        <td :colspan="columns.length" class="px-4 py-6 text-center text-slate-500">{{ emptyMessage }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

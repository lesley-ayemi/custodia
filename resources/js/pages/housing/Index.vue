<script setup lang="ts">
import { onMounted } from 'vue';
import DashboardLayout from '../../layouts/DashboardLayout.vue';
import OccupancyBar from '../../components/OccupancyBar.vue';
import { useHousingStore } from '../../stores/housing';

const store = useHousingStore();

onMounted(() => store.fetchBlocks());
</script>

<template>
    <DashboardLayout>
        <h1 class="text-xl font-semibold text-slate-900">Housing</h1>

        <div class="mt-6 space-y-8">
            <div v-for="block in store.blocks" :key="block.id">
                <h2 class="text-sm font-semibold text-slate-700">{{ block.name }}</h2>
                <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="cell in block.cells" :key="cell.id" class="rounded-lg border border-slate-200 bg-white p-4">
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-slate-900">{{ cell.code }}</span>
                            <span class="text-xs text-slate-500">{{ cell.occupancy }} / {{ cell.capacity }}</span>
                        </div>
                        <OccupancyBar class="mt-3" :occupied="cell.occupancy" :capacity="cell.capacity" />
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

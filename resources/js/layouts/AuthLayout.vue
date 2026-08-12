<script setup lang="ts">
import { ShieldCheck, Building2, ClipboardCheck, HeartPulse } from '@lucide/vue';
</script>

<template>
    <div class="flex min-h-screen bg-white">
        <div class="relative hidden w-1/2 flex-col justify-between overflow-hidden bg-primary-700 p-12 text-white lg:flex">
            <div class="absolute inset-0 bg-gradient-to-br from-primary-600 via-primary-700 to-primary-900" />
            <div class="absolute -top-24 -right-24 h-96 w-96 rounded-full bg-white/10 blur-3xl" />
            <div class="absolute -bottom-32 -left-16 h-96 w-96 rounded-full bg-primary-400/20 blur-3xl" />

            <div class="relative flex items-center gap-2.5">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/15">
                    <ShieldCheck :size="22" />
                </span>
                <span class="text-xl font-bold">Custodia</span>
            </div>

            <div class="relative max-w-md">
                <h1 class="text-4xl font-bold leading-tight">Secure custody &amp; welfare management, in one place.</h1>
                <p class="mt-4 text-primary-100">
                    Track admissions, housing, medical care, court cases and release workflows across your facility with a full audit
                    trail on every action.
                </p>

                <div class="mt-10 grid grid-cols-3 gap-4">
                    <div class="rounded-xl bg-white/10 p-4">
                        <Building2 :size="20" />
                        <p class="mt-2 text-xs font-medium text-primary-100">Housing &amp; movements</p>
                    </div>
                    <div class="rounded-xl bg-white/10 p-4">
                        <HeartPulse :size="20" />
                        <p class="mt-2 text-xs font-medium text-primary-100">Medical records</p>
                    </div>
                    <div class="rounded-xl bg-white/10 p-4">
                        <ClipboardCheck :size="20" />
                        <p class="mt-2 text-xs font-medium text-primary-100">Full audit trail</p>
                    </div>
                </div>
            </div>

            <p class="relative text-xs text-primary-200">© {{ new Date().getFullYear() }} Custodia. All rights reserved.</p>
        </div>

        <div class="flex w-full flex-col items-center justify-center px-6 py-12 lg:w-1/2">
            <div class="w-full max-w-sm">
                <div class="mb-8 flex items-center gap-2.5 lg:hidden">
                    <span class="icon-badge bg-primary-600">
                        <ShieldCheck :size="20" />
                    </span>
                    <span class="text-xl font-bold text-slate-900">Custodia</span>
                </div>

                <slot />
            </div>
        </div>
    </div>
</template>

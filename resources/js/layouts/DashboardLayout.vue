<script setup lang="ts">
import { computed, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import {
    AlertTriangle,
    ArrowLeftRight,
    Building2,
    ClipboardList,
    Gavel,
    GraduationCap,
    HeartPulse,
    History,
    LayoutDashboard,
    LogOut,
    Menu,
    ShieldCheck,
    ShieldHalf,
    Users,
    UserRoundCheck,
    X,
} from '@lucide/vue';

const auth = useAuthStore();
const router = useRouter();
const route = useRoute();

const sidebarOpen = ref(false);
const signingOut = ref(false);

interface NavItem {
    name: string;
    label: string;
    icon: typeof LayoutDashboard;
}

interface NavGroup {
    label: string;
    items: NavItem[];
}

const navGroups = computed<NavGroup[]>(() => {
    const groups: NavGroup[] = [
        {
            label: 'Overview',
            items: [{ name: 'dashboard', label: 'Dashboard', icon: LayoutDashboard }],
        },
        {
            label: 'Custody',
            items: [
                { name: 'prisoners.index', label: 'Prisoners', icon: Users },
                { name: 'housing.index', label: 'Housing', icon: Building2 },
                { name: 'admissions.index', label: 'Admissions', icon: ClipboardList },
                { name: 'movements.index', label: 'Movements', icon: ArrowLeftRight },
            ],
        },
        {
            label: 'Case management',
            items: [
                { name: 'court.index', label: 'Court', icon: Gavel },
                { name: 'incidents.index', label: 'Incidents', icon: AlertTriangle },
            ],
        },
        {
            label: 'Welfare',
            items: [
                { name: 'programmes.index', label: 'Programmes', icon: GraduationCap },
                { name: 'visitors.index', label: 'Visitors', icon: UserRoundCheck },
                { name: 'releases.index', label: 'Releases', icon: LogOut },
            ],
        },
    ];

    if (auth.hasRole('medical', 'admin')) {
        groups.push({
            label: 'Medical',
            items: [{ name: 'medical.index', label: 'Medical', icon: HeartPulse }],
        });
    }

    const admin: NavItem[] = [];
    if (auth.hasRole('admin', 'supervisor')) {
        admin.push({ name: 'audit.index', label: 'Audit log', icon: History });
    }
    if (auth.hasRole('admin')) {
        admin.push({ name: 'users.index', label: 'Staff', icon: ShieldHalf });
    }
    if (admin.length > 0) {
        groups.push({ label: 'Administration', items: admin });
    }

    return groups;
});

function isActive(name: string): boolean {
    return route.name === name || (typeof route.name === 'string' && route.name.startsWith(`${name.split('.')[0]}.`));
}

const initials = computed(() => {
    const name = auth.user?.name ?? '';
    return name
        .split(' ')
        .map((part) => part[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();
});

async function logout(): Promise<void> {
    signingOut.value = true;

    try {
        await auth.logout();
        await router.push({ name: 'login' });
    } finally {
        signingOut.value = false;
    }
}
</script>

<template>
    <div class="min-h-screen bg-slate-50 lg:flex">
        <div v-if="sidebarOpen" class="fixed inset-0 z-30 bg-slate-900/40 lg:hidden" @click="sidebarOpen = false" />

        <aside
            class="fixed inset-y-0 left-0 z-40 flex w-64 shrink-0 flex-col border-r border-slate-100 bg-white transition-transform lg:sticky lg:top-0 lg:h-screen lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="flex items-center justify-between px-5 py-5">
                <div class="flex items-center gap-2.5">
                    <span class="icon-badge bg-primary-600">
                        <ShieldCheck :size="20" />
                    </span>
                    <span class="text-lg font-bold text-slate-900">Custodia</span>
                </div>
                <button type="button" class="text-slate-400 hover:text-slate-600 lg:hidden" @click="sidebarOpen = false">
                    <X :size="20" />
                </button>
            </div>

            <nav class="flex-1 space-y-6 overflow-y-auto px-4 pb-6">
                <div v-for="group in navGroups" :key="group.label">
                    <p class="px-2.5 pb-2 text-[11px] font-semibold tracking-wider text-slate-400 uppercase">{{ group.label }}</p>
                    <div class="space-y-1">
                        <router-link
                            v-for="item in group.items"
                            :key="item.name"
                            :to="{ name: item.name }"
                            class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition"
                            :class="
                                isActive(item.name)
                                    ? 'bg-primary-600 text-white shadow-sm shadow-primary-600/25'
                                    : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'
                            "
                            @click="sidebarOpen = false"
                        >
                            <component :is="item.icon" :size="18" />
                            {{ item.label }}
                        </router-link>
                    </div>
                </div>
            </nav>

            <div class="border-t border-slate-100 p-4">
                <div class="flex items-center gap-3 rounded-xl bg-slate-50 p-3">
                    <span class="icon-badge bg-slate-800 text-sm font-semibold">{{ initials }}</span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-slate-800">{{ auth.user?.name }}</p>
                        <p class="text-xs text-slate-400 capitalize">{{ auth.user?.role }}</p>
                    </div>
                    <button
                        type="button"
                        title="Sign out"
                        :disabled="signingOut"
                        class="shrink-0 rounded-lg p-2 text-slate-400 transition hover:bg-white hover:text-red-600 disabled:opacity-50"
                        @click="logout"
                    >
                        <LogOut :size="16" />
                    </button>
                </div>
            </div>
        </aside>

        <div class="min-w-0 flex-1">
            <header class="sticky top-0 z-20 flex items-center gap-3 border-b border-slate-100 bg-white/80 px-6 py-3 backdrop-blur lg:hidden">
                <button type="button" class="text-slate-500 hover:text-slate-900" @click="sidebarOpen = true">
                    <Menu :size="22" />
                </button>
                <span class="text-base font-bold text-slate-900">Custodia</span>
            </header>

            <main class="mx-auto max-w-6xl px-6 py-8">
                <slot />
            </main>
        </div>
    </div>
</template>

import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import type { UserRole } from '../types/auth';

declare module 'vue-router' {
    interface RouteMeta {
        requiresAuth?: boolean;
        guestOnly?: boolean;
        roles?: UserRole[];
    }
}

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            redirect: { name: 'dashboard' },
        },
        {
            path: '/login',
            name: 'login',
            component: () => import('../pages/Login.vue'),
            meta: { guestOnly: true },
        },
        {
            path: '/dashboard',
            name: 'dashboard',
            component: () => import('../pages/Dashboard.vue'),
            meta: { requiresAuth: true },
        },
        {
            path: '/prisoners',
            name: 'prisoners.index',
            component: () => import('../pages/prisoners/Index.vue'),
            meta: { requiresAuth: true },
        },
        {
            path: '/prisoners/create',
            name: 'prisoners.create',
            component: () => import('../pages/prisoners/Create.vue'),
            meta: { requiresAuth: true, roles: ['officer'] },
        },
        {
            path: '/prisoners/:id',
            name: 'prisoners.show',
            component: () => import('../pages/prisoners/Show.vue'),
            meta: { requiresAuth: true },
        },
    ],
});

router.beforeEach(async (to) => {
    const auth = useAuthStore();

    if (!auth.initialized) {
        await auth.fetchUser();
    }

    if (to.meta.requiresAuth && !auth.isAuthenticated) {
        return { name: 'login' };
    }

    if (to.meta.guestOnly && auth.isAuthenticated) {
        return { name: 'dashboard' };
    }

    if (to.meta.roles && !auth.hasRole(...to.meta.roles)) {
        return { name: 'dashboard' };
    }

    return true;
});

export default router;

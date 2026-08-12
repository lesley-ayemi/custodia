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
            meta: { requiresAuth: true, roles: ['officer', 'admin'] },
        },
        {
            path: '/prisoners/:id',
            name: 'prisoners.show',
            component: () => import('../pages/prisoners/Show.vue'),
            meta: { requiresAuth: true },
        },
        {
            path: '/housing',
            name: 'housing.index',
            component: () => import('../pages/housing/Index.vue'),
            meta: { requiresAuth: true },
        },
        {
            path: '/incidents',
            name: 'incidents.index',
            component: () => import('../pages/incidents/Index.vue'),
            meta: { requiresAuth: true },
        },
        {
            path: '/court',
            name: 'court.index',
            component: () => import('../pages/court/Index.vue'),
            meta: { requiresAuth: true },
        },
        {
            path: '/programmes',
            name: 'programmes.index',
            component: () => import('../pages/programmes/Index.vue'),
            meta: { requiresAuth: true },
        },
        {
            path: '/incidents/create',
            name: 'incidents.create',
            component: () => import('../pages/incidents/Create.vue'),
            meta: { requiresAuth: true, roles: ['officer', 'admin'] },
        },
        {
            path: '/incidents/:id/edit',
            name: 'incidents.edit',
            component: () => import('../pages/incidents/Edit.vue'),
            meta: { requiresAuth: true, roles: ['admin'] },
        },
        {
            path: '/audit',
            name: 'audit.index',
            component: () => import('../pages/audit/Index.vue'),
            meta: { requiresAuth: true, roles: ['admin', 'supervisor'] },
        },
        {
            path: '/users',
            name: 'users.index',
            component: () => import('../pages/users/Index.vue'),
            meta: { requiresAuth: true, roles: ['admin'] },
        },
        {
            path: '/users/create',
            name: 'users.create',
            component: () => import('../pages/users/Create.vue'),
            meta: { requiresAuth: true, roles: ['admin'] },
        },
        {
            path: '/users/:id',
            name: 'users.show',
            component: () => import('../pages/users/Show.vue'),
            meta: { requiresAuth: true, roles: ['admin'] },
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

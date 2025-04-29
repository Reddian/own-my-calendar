import { createRouter, createWebHistory } from 'vue-router';
import store from '../store';
import AppLayout from '../layouts/AppLayout.vue';
import CalendarManager from '../components/calendar/CalendarManager.vue';

const routes = [
    {
        path: '/',
        component: AppLayout,
        children: [
            {
                path: '',
                name: 'dashboard',
                component: () => import('../views/Dashboard.vue'),
                meta: { requiresAuth: true }
            },
            {
                path: 'calendar',
                name: 'calendar',
                component: CalendarManager,
                meta: { requiresAuth: true }
            },
            {
                path: 'tasks',
                name: 'tasks',
                component: () => import('../views/Tasks.vue'),
                meta: { requiresAuth: true }
            },
            {
                path: 'analytics',
                name: 'analytics',
                component: () => import('../views/Analytics.vue'),
                meta: { requiresAuth: true }
            },
            {
                path: 'history',
                name: 'history',
                component: () => import('../views/History.vue'),
                meta: { requiresAuth: true }
            },
            {
                path: 'extension',
                name: 'extension',
                component: () => import('../views/Extension.vue'),
                meta: { requiresAuth: true }
            },
            {
                path: 'settings',
                name: 'settings',
                component: () => import('../views/Settings.vue'),
                meta: { requiresAuth: true }
            }
        ]
    },
    {
        path: '/login',
        name: 'login',
        component: () => import('../views/Login.vue'),
        meta: { guest: true }
    },
    {
        path: '/register',
        name: 'register',
        component: () => import('../views/Register.vue'),
        meta: { guest: true }
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) {
            return savedPosition;
        } else {
            return { top: 0 };
        }
    }
});

// Navigation guard
router.beforeEach((to, from, next) => {
    const isAuthenticated = store.getters['auth/isAuthenticated'];
    
    if (to.matched.some(record => record.meta.requiresAuth)) {
        if (!isAuthenticated) {
            next({ name: 'login' });
        } else {
            next();
        }
    } else if (to.matched.some(record => record.meta.guest)) {
        if (isAuthenticated) {
            next({ name: 'dashboard' });
        } else {
            next();
        }
    } else {
        next();
    }
});

export default router;

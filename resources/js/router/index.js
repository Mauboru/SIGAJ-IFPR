import { createRouter, createWebHistory } from 'vue-router';
import store from './store';

const routes = [
    {
        path: '/',
        redirect: '/login'
    },
    {
        path: '/login',
        name: 'Login',
        component: () => import('../pages/Auth/Login.vue'),
        meta: { requiresGuest: true, layout: false }
    },
    {
        path: '/register',
        name: 'Register',
        component: () => import('../pages/Auth/Register.vue'),
        meta: { requiresGuest: true, layout: false }
    },
    {
        path: '/dashboard',
        name: 'Dashboard',
        component: () => import('../pages/Dashboard.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/usuarios',
        name: 'Usuarios',
        component: () => import('../pages/Usuarios/Index.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/materias',
        name: 'Materias',
        component: () => import('../pages/Materias/Index.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/turmas',
        name: 'Turmas',
        component: () => import('../pages/Turmas/Index.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/aulas',
        name: 'Aulas',
        component: () => import('../pages/Aulas/Index.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/atividades',
        name: 'Atividades',
        component: () => import('../pages/Atividades/Index.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/provas',
        name: 'Provas',
        component: () => import('../pages/Provas/Index.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/notas',
        name: 'Notas',
        component: () => import('../pages/Notas/Index.vue'),
        meta: { requiresAuth: true }
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

// Navigation guards
router.beforeEach((to, from, next) => {
    const isAuthenticated = store.getters.isAuthenticated();
    const requiresAuth = to.matched.some(record => record.meta.requiresAuth);
    const requiresGuest = to.matched.some(record => record.meta.requiresGuest);
    const requiresRole = to.matched.some(record => record.meta.requiresRole);

    if (requiresAuth && !isAuthenticated) {
        next('/login');
    } else if (requiresGuest && isAuthenticated) {
        next('/dashboard');
    } else if (requiresRole && isAuthenticated) {
        const userRole = store.getters.getUser()?.role;
        const requiredRole = to.meta.requiresRole;
        
        if (userRole !== requiredRole) {
            next('/dashboard');
        } else {
            next();
        }
    } else {
        next();
    }
});

export default router;

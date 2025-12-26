<template>
    <div class="min-h-screen bg-gray-50">
        <nav class="bg-white shadow-lg border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="flex-shrink-0 flex items-center">
                            <h1 class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                                SIGAJ-IFPR
                            </h1>
                            <span class="ml-2 text-xs text-gray-500">Sistema de Gestão Acadêmica</span>
                        </div>
                        <div class="hidden sm:ml-8 sm:flex sm:space-x-4">
                            <router-link
                                to="/dashboard"
                                class="inline-flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors"
                                :class="$route.path === '/dashboard' ? 'bg-indigo-100 text-indigo-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'"
                            >
                                <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                                Dashboard
                            </router-link>
                            <template v-if="user?.role === 'professor'">
                                <router-link
                                    to="/usuarios"
                                    class="inline-flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors"
                                    :class="$route.path === '/usuarios' ? 'bg-indigo-100 text-indigo-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'"
                                >
                                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    Usuários
                                </router-link>
                                <router-link
                                    to="/materias"
                                    class="inline-flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors"
                                    :class="$route.path === '/materias' ? 'bg-indigo-100 text-indigo-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'"
                                >
                                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                    Matérias
                                </router-link>
                                <router-link
                                    to="/turmas"
                                    class="inline-flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors"
                                    :class="$route.path === '/turmas' ? 'bg-indigo-100 text-indigo-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'"
                                >
                                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    Turmas
                                </router-link>
                                <router-link
                                    to="/aulas"
                                    class="inline-flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors"
                                    :class="$route.path === '/aulas' ? 'bg-indigo-100 text-indigo-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'"
                                >
                                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Aulas
                                </router-link>
                                <router-link
                                    to="/atividades"
                                    class="inline-flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors"
                                    :class="$route.path === '/atividades' ? 'bg-indigo-100 text-indigo-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'"
                                >
                                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    Atividades
                                </router-link>
                                <router-link
                                    to="/provas"
                                    class="inline-flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors"
                                    :class="$route.path === '/provas' ? 'bg-indigo-100 text-indigo-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'"
                                >
                                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Provas
                                </router-link>
                            </template>
                            <router-link
                                to="/notas"
                                class="inline-flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors"
                                :class="$route.path === '/notas' ? 'bg-indigo-100 text-indigo-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'"
                            >
                                <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Notas
                            </router-link>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center space-x-2">
                            <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center">
                                <span class="text-indigo-600 font-semibold text-sm">{{ user?.name?.charAt(0)?.toUpperCase() }}</span>
                            </div>
                            <div class="hidden md:block">
                                <p class="text-sm font-medium text-gray-900">{{ user?.name }}</p>
                                <p class="text-xs text-gray-500">{{ user?.role === 'professor' ? 'Professor' : 'Aluno' }}</p>
                            </div>
                        </div>
                        <button
                            @click="logout"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors"
                        >
                            <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Sair
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <slot />
        </main>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import store from '../router/store';
import axios from 'axios';

const router = useRouter();
const user = computed(() => store.getters.getUser());

const logout = async () => {
    try {
        await axios.post('/auth/logout');
    } catch (error) {
        console.error('Erro ao fazer logout:', error);
    } finally {
        store.clearAuth();
        router.push('/login');
    }
};
</script>

<template>
  <nav class="sticky top-0 z-30 bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
    <div class="px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-16">
        <!-- Lado esquerdo: Menu hamburger e busca -->
        <div class="flex items-center space-x-4">
          <!-- Botão hamburger (mobile) -->
          <button
            @click="$emit('toggle-sidebar')"
            class="lg:hidden p-2 rounded-md text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors"
            aria-label="Toggle sidebar"
          >
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>

          <!-- Título da página -->
          <div class="flex items-center">
            <h2 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-gray-100">
              {{ pageTitle }}
            </h2>
          </div>
        </div>

        <!-- Lado direito: Ações do usuário -->
        <div class="flex items-center space-x-2 sm:space-x-4">
          <!-- Toggle de tema -->
          <button
            @click="toggleTheme"
            class="p-2 rounded-md text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors"
            :title="isDark ? 'Alternar para modo claro' : 'Alternar para modo escuro'"
          >
            <svg v-if="!isDark" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
            <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
          </button>

          <!-- Botão logout -->
          <button
            @click="handleLogout"
            class="inline-flex items-center px-3 py-2 sm:px-4 sm:py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 transition-colors"
          >
            <svg class="mr-1.5 sm:mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            <span class="hidden sm:inline">Sair</span>
          </button>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import store from '../router/store'
import axios from 'axios'
import { useTheme } from '../composables/useTheme'

const route = useRoute()
const router = useRouter()
const { isDark, toggleTheme } = useTheme()

// Título da página baseado na rota
const pageTitle = computed(() => {
  const titles = {
    '/dashboard': 'Dashboard',
    '/usuarios': 'Usuários',
    '/materias': 'Matérias',
    '/turmas': 'Turmas',
    '/aulas': 'Aulas',
    '/atividades': 'Atividades',
    '/provas': 'Provas',
    '/notas': 'Notas'
  }
  return titles[route.path] || 'Dashboard'
})

const handleLogout = async () => {
  try {
    await axios.post('/auth/logout')
  } catch (error) {
    console.error('Erro ao fazer logout:', error)
  } finally {
    store.clearAuth()
    router.push('/login')
  }
}
</script>


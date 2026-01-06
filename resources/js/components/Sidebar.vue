<template>
  <!-- Overlay para mobile -->
  <div
    v-if="isMobile && isOpen"
    class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden transition-opacity duration-300"
    @click="closeSidebar"
  ></div>

  <!-- Sidebar -->
  <aside
    :class="[
      'fixed top-0 left-0 h-full bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 z-50 transform transition-transform duration-300 ease-in-out flex flex-col',
      isMobile ? (isOpen ? 'translate-x-0' : '-translate-x-full') : 'translate-x-0',
      'w-64'
    ]"
  >
    <!-- Logo e Header -->
    <div class="flex items-center justify-between h-16 px-6 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center space-x-2">
        <div class="h-8 w-8 rounded-lg bg-gradient-to-br from-indigo-600 to-purple-600 flex items-center justify-center">
          <span class="text-white font-bold text-sm">S</span>
        </div>
        <div>
          <h1 class="text-lg font-bold bg-gradient-to-r from-indigo-600 to-purple-600 dark:from-indigo-400 dark:to-purple-400 bg-clip-text text-transparent">
            SIGAJ-IFPR
          </h1>
          <p class="text-xs text-gray-500 dark:text-gray-400">Sistema Acadêmico</p>
        </div>
      </div>
      <!-- Botão fechar (mobile) -->
      <button
        v-if="isMobile"
        @click="closeSidebar"
        class="lg:hidden p-2 rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
      >
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

    <!-- Navegação -->
    <nav class="flex-1 overflow-y-auto py-4 px-3 pb-20">
      <div class="space-y-1">
        <!-- Dashboard -->
        <router-link
          to="/dashboard"
          @click="closeSidebarOnMobile"
          class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 group"
          :class="
            $route.path === '/dashboard'
              ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300'
              : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
          "
        >
          <svg class="mr-3 h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
          </svg>
          <span>Dashboard</span>
        </router-link>

        <!-- Seção Professor (apenas para professores) -->
        <template v-if="user?.role === 'professor'">
          <div class="pt-4 pb-2">
            <p class="px-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Gestão
            </p>
          </div>

          <router-link
            to="/usuarios"
            @click="closeSidebarOnMobile"
            class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 group"
            :class="
              $route.path === '/usuarios'
                ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300'
                : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
            "
          >
            <svg class="mr-3 h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <span>Usuários</span>
          </router-link>

          <router-link
            to="/materias"
            @click="closeSidebarOnMobile"
            class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 group"
            :class="
              $route.path === '/materias'
                ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300'
                : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
            "
          >
            <svg class="mr-3 h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            <span>Matérias</span>
          </router-link>

          <router-link
            to="/turmas"
            @click="closeSidebarOnMobile"
            class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 group"
            :class="
              $route.path === '/turmas'
                ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300'
                : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
            "
          >
            <svg class="mr-3 h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span>Turmas</span>
          </router-link>

          <div class="pt-4 pb-2">
            <p class="px-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Conteúdo
            </p>
          </div>

          <router-link
            to="/aulas"
            @click="closeSidebarOnMobile"
            class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 group"
            :class="
              $route.path === '/aulas'
                ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300'
                : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
            "
          >
            <svg class="mr-3 h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span>Aulas</span>
          </router-link>

          <router-link
            to="/atividades"
            @click="closeSidebarOnMobile"
            class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 group"
            :class="
              $route.path === '/atividades'
                ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300'
                : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
            "
          >
            <svg class="mr-3 h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <span>Atividades</span>
          </router-link>

          <router-link
            to="/provas"
            @click="closeSidebarOnMobile"
            class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 group"
            :class="
              $route.path === '/provas'
                ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300'
                : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
            "
          >
            <svg class="mr-3 h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span>Provas</span>
          </router-link>
        </template>

        <!-- Seção Aluno/Comum -->
        <div class="pt-4 pb-2">
          <p class="px-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
            Acadêmico
          </p>
        </div>

        <router-link
          to="/notas"
          @click="closeSidebarOnMobile"
          class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 group"
          :class="
            $route.path === '/notas'
              ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300'
              : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
          "
        >
          <svg class="mr-3 h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span>Notas</span>
        </router-link>
      </div>
    </nav>

    <!-- Footer da Sidebar -->
    <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
      <div class="flex items-center space-x-3 px-3 py-2.5 rounded-lg bg-gray-50 dark:bg-gray-700/50">
        <div class="h-10 w-10 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center flex-shrink-0">
          <span class="text-indigo-600 dark:text-indigo-300 font-semibold text-sm">
            {{ user?.name?.charAt(0)?.toUpperCase() }}
          </span>
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
            {{ user?.name }}
          </p>
          <p class="text-xs text-gray-500 dark:text-gray-400">
            {{ user?.role === 'professor' ? 'Professor' : 'Aluno' }}
          </p>
        </div>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import store from '../router/store'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close'])

const user = computed(() => store.getters.getUser())

// Detectar se é mobile de forma reativa
const isMobile = ref(false)

const checkMobile = () => {
  if (typeof window !== 'undefined') {
    isMobile.value = window.innerWidth < 1024
  }
}

onMounted(() => {
  checkMobile()
  window.addEventListener('resize', checkMobile)
})

onUnmounted(() => {
  if (typeof window !== 'undefined') {
    window.removeEventListener('resize', checkMobile)
  }
})

const closeSidebar = () => {
  emit('close')
}

const closeSidebarOnMobile = () => {
  if (isMobile.value) {
    closeSidebar()
  }
}
</script>


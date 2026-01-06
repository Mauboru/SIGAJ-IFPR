<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 flex">
    <!-- Sidebar -->
    <Sidebar :is-open="sidebarOpen" @close="sidebarOpen = false" />

    <!-- Conteúdo principal -->
    <div class="flex-1 flex flex-col lg:ml-64 transition-all duration-300">
      <!-- Navbar -->
      <Navbar @toggle-sidebar="sidebarOpen = !sidebarOpen" />

      <!-- Main content -->
      <main class="flex-1 overflow-x-hidden">
        <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto w-full">
          <slot />
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import Sidebar from './Sidebar.vue'
import Navbar from './Navbar.vue'

const sidebarOpen = ref(false)

// Fechar sidebar ao redimensionar para desktop
const handleResize = () => {
  if (window.innerWidth >= 1024) {
    sidebarOpen.value = false
  }
}

onMounted(() => {
  window.addEventListener('resize', handleResize)
})

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
})
</script>

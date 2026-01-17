<template>
  <Teleport to="body">
    <div class="fixed top-4 right-4 z-50 space-y-2">
      <TransitionGroup name="toast" tag="div">
        <div
          v-for="toast in toasts"
          :key="toast.id"
          :class="[
            'min-w-[300px] max-w-md rounded-lg shadow-lg p-4 flex items-start gap-3',
            toast.type === 'success' ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800' : '',
            toast.type === 'error' ? 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800' : '',
            toast.type === 'warning' ? 'bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800' : '',
            toast.type === 'info' ? 'bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800' : '',
          ]"
        >
          <!-- Ícone -->
          <div
            :class="[
              'flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center',
              toast.type === 'success' ? 'bg-green-100 dark:bg-green-800 text-green-600 dark:text-green-300' : '',
              toast.type === 'error' ? 'bg-red-100 dark:bg-red-800 text-red-600 dark:text-red-300' : '',
              toast.type === 'warning' ? 'bg-yellow-100 dark:bg-yellow-800 text-yellow-600 dark:text-yellow-300' : '',
              toast.type === 'info' ? 'bg-blue-100 dark:bg-blue-800 text-blue-600 dark:text-blue-300' : '',
            ]"
          >
            <svg
              v-if="toast.type === 'success'"
              class="w-4 h-4"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <svg
              v-else-if="toast.type === 'error'"
              class="w-4 h-4"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <svg
              v-else-if="toast.type === 'warning'"
              class="w-4 h-4"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <svg
              v-else
              class="w-4 h-4"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>

          <!-- Conteúdo -->
          <div class="flex-1 min-w-0">
            <p
              :class="[
                'text-sm font-medium',
                toast.type === 'success' ? 'text-green-800 dark:text-green-200' : '',
                toast.type === 'error' ? 'text-red-800 dark:text-red-200' : '',
                toast.type === 'warning' ? 'text-yellow-800 dark:text-yellow-200' : '',
                toast.type === 'info' ? 'text-blue-800 dark:text-blue-200' : '',
              ]"
            >
              {{ toast.message }}
            </p>
          </div>

          <!-- Botão fechar -->
          <button
            @click="removeToast(toast.id)"
            :class="[
              'flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors',
              toast.type === 'success' ? 'hover:text-green-600 dark:hover:text-green-400' : '',
              toast.type === 'error' ? 'hover:text-red-600 dark:hover:text-red-400' : '',
              toast.type === 'warning' ? 'hover:text-yellow-600 dark:hover:text-yellow-400' : '',
              toast.type === 'info' ? 'hover:text-blue-600 dark:hover:text-blue-400' : '',
            ]"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const toasts = ref([])

let toastIdCounter = 0

const addToast = (message, type = 'info', duration = 5000) => {
  const id = ++toastIdCounter
  const toast = {
    id,
    message,
    type,
  }
  
  toasts.value.push(toast)
  
  if (duration > 0) {
    setTimeout(() => {
      removeToast(id)
    }, duration)
  }
  
  return id
}

const removeToast = (id) => {
  const index = toasts.value.findIndex(t => t.id === id)
  if (index > -1) {
    toasts.value.splice(index, 1)
  }
}

const clearAll = () => {
  toasts.value = []
}

// Expor métodos globalmente
const toastMethods = {
  success: (message, duration = 5000) => addToast(message, 'success', duration),
  error: (message, duration = 5000) => addToast(message, 'error', duration),
  warning: (message, duration = 5000) => addToast(message, 'warning', duration),
  info: (message, duration = 5000) => addToast(message, 'info', duration),
  clear: clearAll,
}

// Expor imediatamente e também no onMounted para garantir
if (typeof window !== 'undefined') {
  window.toast = toastMethods
}

onMounted(() => {
  if (typeof window !== 'undefined') {
    window.toast = toastMethods
  }
})

defineExpose({
  addToast,
  removeToast,
  clearAll,
})
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(100%);
}

.toast-move {
  transition: transform 0.3s ease;
}
</style>

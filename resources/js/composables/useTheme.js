import { ref } from 'vue'

// Estado global compartilhado
const isDark = ref(false)

// Aplicar tema ao documento
const applyTheme = () => {
  if (typeof document === 'undefined') return
  
  const html = document.documentElement
  
  // Sempre remover primeiro para garantir limpeza
  html.classList.remove('dark')
  
  // Adicionar se necessário (Tailwind usa a classe 'dark' no elemento html)
  if (isDark.value) {
    html.classList.add('dark')
  }
}

// Carregar tema do localStorage ou usar preferência do sistema
const loadTheme = () => {
  if (typeof window === 'undefined') return
  
  const savedTheme = localStorage.getItem('theme')
  if (savedTheme) {
    isDark.value = savedTheme === 'dark'
  } else {
    // Verificar preferência do sistema
    isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches
  }
  applyTheme()
}

// Observar mudanças na preferência do sistema
const watchSystemPreference = () => {
  if (typeof window === 'undefined') return
  
  const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)')
  const handleChange = (e) => {
    // Só aplicar se não houver tema salvo
    if (!localStorage.getItem('theme')) {
      isDark.value = e.matches
      applyTheme()
    }
  }
  mediaQuery.addEventListener('change', handleChange)
}

// Inicializar tema imediatamente (antes do Vue montar)
if (typeof window !== 'undefined') {
  loadTheme()
  watchSystemPreference()
}

export function useTheme() {
  // Alternar tema
  const toggleTheme = () => {
    // Alternar o valor ANTES de aplicar
    isDark.value = !isDark.value
    const newValue = isDark.value
    const theme = newValue ? 'dark' : 'light'
    
    // Salvar no localStorage
    localStorage.setItem('theme', theme)
    
    // Aplicar tema imediatamente no DOM
    applyTheme()
  }

  // Definir tema específico
  const setTheme = (theme) => {
    isDark.value = theme === 'dark'
    localStorage.setItem('theme', theme)
    applyTheme()
  }

  // Retornar a ref diretamente (Vue faz unwrap automático nos templates)
  return {
    isDark,
    toggleTheme,
    setTheme,
    loadTheme,
  }
}


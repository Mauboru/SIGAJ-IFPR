import '../css/app.css'
// Importar composable de tema para inicializar antes do app
import './composables/useTheme'
import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import store from './router/store'
import axios from 'axios'

// Configurar axios
axios.defaults.baseURL = '/api'
axios.defaults.headers.common['Accept'] = 'application/json'
// Não definir Content-Type padrão - será definido automaticamente pelo navegador (FormData) ou pelo axios (JSON)

// Configurar token se existir
const token = localStorage.getItem('token')
if (token) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
}

// Interceptor para erros 401
axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401) {
            store.clearAuth()
            if (window.location.pathname !== '/login') {
                window.location.href = '/login'
            }
        }
        return Promise.reject(error)
    }
)

// Inicializar autenticação
store.initAuth()

const app = createApp(App)
app.use(router)
app.mount('#app')

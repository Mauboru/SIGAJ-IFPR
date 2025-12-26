import { ref, computed } from 'vue';
import axios from 'axios';

// Estado reativo
const token = ref(localStorage.getItem('token') || null);
const user = ref(JSON.parse(localStorage.getItem('user') || 'null'));

// Configurar axios com token se existir
if (token.value) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
}

const store = {
    // Getters
    getters: {
        isAuthenticated() {
            return !!token.value;
        },
        getUser() {
            return user.value;
        },
        getToken() {
            return token.value;
        },
        isProfessor() {
            return user.value?.role === 'professor';
        },
        isAluno() {
            return user.value?.role === 'aluno';
        }
    },

    // Actions
    setAuth({ user: userData, token: tokenData }) {
        token.value = tokenData;
        user.value = userData;
        
        localStorage.setItem('token', tokenData);
        localStorage.setItem('user', JSON.stringify(userData));
        
        axios.defaults.headers.common['Authorization'] = `Bearer ${tokenData}`;
    },

    clearAuth() {
        token.value = null;
        user.value = null;
        
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        
        delete axios.defaults.headers.common['Authorization'];
    },

    initAuth() {
        // Verificar se token ainda é válido
        if (token.value) {
            axios.get('/auth/me')
                .then(response => {
                    store.setAuth({
                        user: response.data,
                        token: token.value
                    });
                })
                .catch(() => {
                    store.clearAuth();
                });
        }
    }
};

export default store;


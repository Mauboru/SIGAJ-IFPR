<template>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 py-12 px-4 sm:px-6 lg:px-8 relative">
        <button
            @click="toggleTheme"
            class="absolute top-4 right-4 inline-flex items-center px-3 py-2 rounded-md text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white transition-colors"
            :title="isDark ? 'Alternar para modo claro' : 'Alternar para modo escuro'"
        >
            <svg v-if="!isDark" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
            <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
        </button>
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <h1 class="text-4xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 dark:from-indigo-400 dark:to-purple-400 bg-clip-text text-transparent mb-2">
                    SIGAJ-IFPR
                </h1>
                <p class="text-gray-600 dark:text-gray-400 text-sm">Sistema de Gestão Acadêmica</p>
                <h2 class="mt-6 text-2xl font-bold text-gray-900 dark:text-gray-100">
                    Entre na sua conta
                </h2>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-8 border border-gray-200 dark:border-gray-700">
                <form class="space-y-6" @submit.prevent="handleLogin">
                    <div>
                        <FormInput
                            id="email"
                            v-model="form.email"
                            type="email"
                            label="E-mail"
                            placeholder="seu@email.com"
                            required
                            :error="errors.email"
                        />
                    </div>
                    <div>
                        <FormInput
                            id="password"
                            v-model="form.password"
                            type="password"
                            label="Senha"
                            placeholder="********"
                            required
                            :error="errors.password"
                        />
                    </div>

                    <div v-if="errorMessage" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 px-4 py-3 rounded">
                        {{ errorMessage }}
                    </div>

                    <div>
                        <Button type="submit" :loading="loading" class="w-full bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600">
                            Entrar
                        </Button>
                    </div>

                    <div class="text-center">
                        <router-link to="/register" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 dark:hover:text-indigo-300 font-medium">
                            Não tem uma conta? Registre-se
                        </router-link>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import store from '../../router/store';
import FormInput from '../../components/FormInput.vue';
import Button from '../../components/Button.vue';
import { useTheme } from '../../composables/useTheme';

const { isDark, toggleTheme } = useTheme();

const router = useRouter();

const form = ref({
    email: '',
    password: ''
});

const errors = ref({});
const errorMessage = ref('');
const loading = ref(false);

const handleLogin = async () => {
    errors.value = {};
    errorMessage.value = '';
    loading.value = true;

    try {
        const response = await axios.post('/auth/login', form.value);
        store.setAuth({ user: response.data.user, token: response.data.token });
        router.push('/dashboard');
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
        } else {
            errorMessage.value = error.response?.data?.message || 'Erro ao fazer login';
        }
    } finally {
        loading.value = false;
    }
};
</script>

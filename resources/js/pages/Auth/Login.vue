<template>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-50 via-white to-purple-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <h1 class="text-4xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent mb-2">
                    SIGAJ-IFPR
                </h1>
                <p class="text-gray-600 text-sm">Sistema de Gestão Acadêmica</p>
                <h2 class="mt-6 text-2xl font-bold text-gray-900">
                    Entre na sua conta
                </h2>
            </div>
            <div class="bg-white rounded-lg shadow-xl p-8">
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

                    <div v-if="errorMessage" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                        {{ errorMessage }}
                    </div>

                    <div>
                        <Button type="submit" :loading="loading" class="w-full bg-indigo-600 hover:bg-indigo-700">
                            Entrar
                        </Button>
                    </div>

                    <div class="text-center">
                        <router-link to="/register" class="text-indigo-600 hover:text-indigo-500 font-medium">
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

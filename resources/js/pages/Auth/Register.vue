<template>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-50 via-white to-purple-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <h1 class="text-4xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent mb-2">
                    SIGAJ-IFPR
                </h1>
                <p class="text-gray-600 text-sm">Sistema de Gestão Acadêmica</p>
                <h2 class="mt-6 text-2xl font-bold text-gray-900">
                    Crie sua conta
                </h2>
            </div>
            <div class="bg-white rounded-lg shadow-xl p-8">
                <form class="space-y-4" @submit.prevent="handleRegister">
                    <FormInput
                        id="name"
                        v-model="form.name"
                        type="text"
                        label="Nome"
                        placeholder="Seu nome completo"
                        required
                        :error="errors.name"
                    />
                    <FormInput
                        id="email"
                        v-model="form.email"
                        type="email"
                        label="E-mail"
                        placeholder="seu@email.com"
                        required
                        :error="errors.email"
                    />
                    <FormInput
                        id="password"
                        v-model="form.password"
                        type="password"
                        label="Senha"
                        placeholder="********"
                        required
                        :error="errors.password"
                    />
                    <FormInput
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        label="Confirmar Senha"
                        placeholder="********"
                        required
                        :error="errors.password_confirmation"
                    />
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Perfil
                        </label>
                        <select
                            v-model="form.role"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            required
                        >
                            <option value="">Selecione...</option>
                            <option value="professor">Professor</option>
                            <option value="aluno">Aluno</option>
                        </select>
                        <p v-if="errors.role" class="mt-1 text-sm text-red-600">{{ errors.role }}</p>
                    </div>
                    <FormInput
                        id="instituicao"
                        v-model="form.instituicao"
                        type="text"
                        label="Instituição"
                        placeholder="Nome da instituição (opcional)"
                        :error="errors.instituicao"
                    />

                    <div v-if="errorMessage" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                        {{ errorMessage }}
                    </div>

                    <div>
                        <Button type="submit" :loading="loading" class="w-full bg-indigo-600 hover:bg-indigo-700">
                            Registrar
                        </Button>
                    </div>

                    <div class="text-center">
                        <router-link to="/login" class="text-indigo-600 hover:text-indigo-500 font-medium">
                            Já tem uma conta? Faça login
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
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: '',
    instituicao: ''
});

const errors = ref({});
const errorMessage = ref('');
const loading = ref(false);

const handleRegister = async () => {
    errors.value = {};
    errorMessage.value = '';
    loading.value = true;

    try {
        const response = await axios.post('/auth/register', form.value);
        store.setAuth({ user: response.data.user, token: response.data.token });
        router.push('/dashboard');
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
        } else {
            errorMessage.value = error.response?.data?.message || 'Erro ao registrar';
        }
    } finally {
        loading.value = false;
    }
};
</script>

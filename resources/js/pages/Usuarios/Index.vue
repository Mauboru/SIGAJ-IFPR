<template>
    <div class="p-6 min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ user?.role === 'professor' ? 'Alunos' : 'Usuários' }}</h1>
                <Button @click="openCreateModal" v-if="user?.role === 'professor'">
                    Novo Aluno
                </Button>
            </div>

            <!-- Lista vazia -->
            <div v-if="usuarios.length === 0" class="bg-white dark:bg-gray-800 rounded-lg shadow p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Nenhum {{ user?.role === 'professor' ? 'aluno' : 'usuário' }} encontrado</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Comece cadastrando um novo {{ user?.role === 'professor' ? 'aluno' : 'usuário' }}.</p>
            </div>

            <!-- Grid de cards -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div 
                    v-for="usuario in usuarios" 
                    :key="usuario.id" 
                    class="bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition-shadow border border-gray-200 dark:border-gray-700 overflow-hidden"
                >
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <img
                                    v-if="usuario.foto"
                                    :src="`/storage/${usuario.foto}`"
                                    :alt="usuario.name"
                                    class="h-16 w-16 rounded-full object-cover ring-2 ring-gray-200 dark:ring-gray-700"
                                />
                                <div
                                    v-else
                                    class="h-16 w-16 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 dark:from-indigo-600 dark:to-purple-700 flex items-center justify-center ring-2 ring-gray-200 dark:ring-gray-700"
                                >
                                    <span class="text-white text-xl font-semibold">{{ usuario.name.charAt(0).toUpperCase() }}</span>
                                </div>
                            </div>
                            <div class="ml-4 flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-lg font-semibold text-gray-900 dark:text-white truncate">
                                            {{ usuario.name }}
                                        </p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
                                            {{ usuario.email }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <span 
                                        :class="[
                                            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                            usuario.role === 'professor' 
                                                ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' 
                                                : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'
                                        ]"
                                    >
                                        {{ usuario.role === 'professor' ? 'Professor' : 'Aluno' }}
                                    </span>
                                    <span v-if="usuario.instituicao" class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                        {{ usuario.instituicao }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 dark:bg-gray-700 px-6 py-3 flex justify-end space-x-2 border-t border-gray-200 dark:border-gray-600">
                        <button
                            @click="editUsuario(usuario)"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 dark:text-indigo-300 bg-indigo-100 dark:bg-indigo-900 hover:bg-indigo-200 dark:hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-700 transition-colors"
                        >
                            <svg class="mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Editar
                        </button>
                    </div>
                </div>
            </div>

            <Modal :show="showCreateModal || showEditModal" :title="editingUsuario ? (user?.role === 'professor' ? 'Editar Aluno' : 'Editar Usuário') : (user?.role === 'professor' ? 'Novo Aluno' : 'Novo Usuário')" @close="closeModal">
                <form @submit.prevent="saveUsuario">
                    <div class="space-y-6">
                        <!-- Preview de foto (apenas na edição) -->
                        <div v-if="editingUsuario && editingUsuario.foto" class="flex justify-center">
                            <div class="relative">
                                <img
                                    :src="`/storage/${editingUsuario.foto}`"
                                    :alt="editingUsuario.name"
                                    class="h-24 w-24 rounded-full object-cover ring-4 ring-gray-200 dark:ring-gray-700"
                                />
                            </div>
                        </div>

                        <!-- Informações Básicas -->
                        <div class="space-y-4">
                            <div>
                                <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3 flex items-center">
                                    <svg class="mr-2 h-4 w-4 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    Informações Básicas
                                </h3>
                            </div>
                            
                            <FormInput
                                id="name"
                                v-model="form.name"
                                label="Nome Completo"
                                placeholder="Digite o nome completo"
                                required
                                :error="errors.name"
                            />
                            
                            <FormInput
                                id="email"
                                v-model="form.email"
                                type="email"
                                label="E-mail"
                                placeholder="exemplo@email.com"
                                required
                                :error="errors.email"
                            />
                        </div>

                        <!-- Configurações de Acesso -->
                        <div class="space-y-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <div>
                                <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3 flex items-center">
                                    <svg class="mr-2 h-4 w-4 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    Configurações de Acesso
                                </h3>
                            </div>

                            <div v-if="user?.role !== 'professor' || editingUsuario">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Perfil
                                </label>
                                <select
                                    v-model="form.role"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-indigo-500 dark:focus:ring-indigo-400 sm:text-sm px-3 py-2 transition-colors"
                                    required
                                >
                                    <option value="">Selecione o perfil...</option>
                                    <option value="professor">Professor</option>
                                    <option value="aluno">Aluno</option>
                                </select>
                                <p v-if="errors.role" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.role }}</p>
                            </div>
                            <div v-else class="hidden">
                                <input type="hidden" v-model="form.role" />
                            </div>

                            <div>
                                <FormInput
                                    id="password"
                                    v-model="form.password"
                                    type="password"
                                    :label="editingUsuario ? 'Nova Senha (deixe em branco para manter a atual)' : 'Senha'"
                                    placeholder="Mínimo 8 caracteres"
                                    :required="!editingUsuario"
                                    :error="errors.password"
                                />
                                <p v-if="editingUsuario" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Deixe em branco se não deseja alterar a senha
                                </p>
                            </div>

                            <FormInput
                                v-if="form.password || !editingUsuario"
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                label="Confirmar Senha"
                                placeholder="Digite a senha novamente"
                                :required="!editingUsuario && form.password"
                                :error="errors.password_confirmation"
                            />
                        </div>

                        <!-- Informações Adicionais -->
                        <div class="space-y-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <div>
                                <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3 flex items-center">
                                    <svg class="mr-2 h-4 w-4 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    Informações Adicionais
                                </h3>
                            </div>
                            
                            <FormInput
                                id="instituicao"
                                v-model="form.instituicao"
                                label="Instituição"
                                placeholder="Nome da instituição (opcional)"
                                :error="errors.instituicao"
                            />
                        </div>
                    </div>
                </form>
                <template #footer>
                    <Button variant="secondary" @click="closeModal" class="mr-3">
                        Cancelar
                    </Button>
                    <Button @click="saveUsuario" :loading="loading">
                        Salvar
                    </Button>
                </template>
            </Modal>
        </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import store from '../../router/store';
import FormInput from '../../components/FormInput.vue';
import Button from '../../components/Button.vue';
import Modal from '../../components/Modal.vue';

const user = computed(() => store.getters.getUser());
const usuarios = ref([]);
const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingUsuario = ref(null);
const loading = ref(false);
const errors = ref({});

const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: '',
    instituicao: ''
});

// Inicializar role como 'aluno' se for professor
const initializeForm = () => {
    if (user.value?.role === 'professor') {
        form.value.role = 'aluno';
    }
};

const loadUsuarios = async () => {
    try {
        // Se for professor, carregar apenas alunos
        const url = user.value?.role === 'professor' ? '/usuarios?role=aluno' : '/usuarios';
        const response = await axios.get(url);
        usuarios.value = response.data;
    } catch (error) {
        console.error('Erro ao carregar usuários:', error);
    }
};

const openCreateModal = () => {
    form.value = {
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        role: 'aluno', // Fixar como aluno quando professor criar
        instituicao: ''
    };
    errors.value = {};
    showCreateModal.value = true;
};

const editUsuario = (usuario) => {
    editingUsuario.value = usuario;
    form.value = {
        name: usuario.name,
        email: usuario.email,
        password: '',
        password_confirmation: '',
        role: usuario.role,
        instituicao: usuario.instituicao || ''
    };
    showEditModal.value = true;
};

const closeModal = () => {
    showCreateModal.value = false;
    showEditModal.value = false;
    editingUsuario.value = null;
    form.value = {
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        role: user.value?.role === 'professor' ? 'aluno' : '',
        instituicao: ''
    };
    errors.value = {};
};

const saveUsuario = async () => {
    errors.value = {};
    loading.value = true;

    try {
        if (editingUsuario.value) {
            await axios.put(`/usuarios/${editingUsuario.value.id}`, form.value);
        } else {
            await axios.post('/usuarios', form.value);
        }
        await loadUsuarios();
        closeModal();
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
        } else {
            alert('Erro ao salvar usuário');
        }
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    initializeForm();
    loadUsuarios();
});
</script>


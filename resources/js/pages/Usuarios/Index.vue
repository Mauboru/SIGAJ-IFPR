<template>
    <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Usuários</h1>
                <Button @click="showCreateModal = true" v-if="user?.role === 'professor'">
                    Novo Usuário
                </Button>
            </div>

            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <ul class="divide-y divide-gray-200">
                    <li v-for="usuario in usuarios" :key="usuario.id" class="px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <img
                                        v-if="usuario.foto"
                                        :src="`/storage/${usuario.foto}`"
                                        :alt="usuario.name"
                                        class="h-10 w-10 rounded-full"
                                    />
                                    <div
                                        v-else
                                        class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center"
                                    >
                                        <span class="text-gray-600 font-medium">{{ usuario.name.charAt(0) }}</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ usuario.name }}</div>
                                    <div class="text-sm text-gray-500">{{ usuario.email }}</div>
                                    <div class="text-xs text-gray-400">
                                        {{ usuario.role === 'professor' ? 'Professor' : 'Aluno' }}
                                        <span v-if="usuario.instituicao"> • {{ usuario.instituicao }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex space-x-2">
                                <button
                                    @click="editUsuario(usuario)"
                                    class="text-indigo-600 hover:text-indigo-900"
                                >
                                    Editar
                                </button>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            <Modal :show="showCreateModal || showEditModal" :title="editingUsuario ? 'Editar Usuário' : 'Novo Usuário'" @close="closeModal">
                <form @submit.prevent="saveUsuario">
                    <div class="space-y-4">
                        <FormInput
                            id="name"
                            v-model="form.name"
                            label="Nome"
                            required
                            :error="errors.name"
                        />
                        <FormInput
                            id="email"
                            v-model="form.email"
                            type="email"
                            label="E-mail"
                            required
                            :error="errors.email"
                        />
                        <FormInput
                            id="password"
                            v-model="form.password"
                            type="password"
                            label="Senha"
                            :required="!editingUsuario"
                            :error="errors.password"
                        />
                        <FormInput
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            label="Confirmar Senha"
                            :required="!editingUsuario && form.password"
                            :error="errors.password_confirmation"
                        />
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Perfil</label>
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
                            label="Instituição"
                            :error="errors.instituicao"
                        />
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

const loadUsuarios = async () => {
    try {
        const response = await axios.get('/usuarios');
        usuarios.value = response.data;
    } catch (error) {
        console.error('Erro ao carregar usuários:', error);
    }
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
        role: '',
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
    loadUsuarios();
});
</script>


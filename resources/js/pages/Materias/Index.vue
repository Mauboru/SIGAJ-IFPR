<template>
    <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Matérias</h1>
                <Button @click="showCreateModal = true" v-if="user?.role === 'professor'">
                    Nova Matéria
                </Button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="materia in materias"
                    :key="materia.id"
                    class="bg-white shadow rounded-lg p-6 cursor-pointer hover:shadow-md transition"
                    @click="viewMateria(materia)"
                >
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ materia.nome }}</h3>
                    <p class="text-sm text-gray-600 mb-4">{{ materia.descricao || 'Sem descrição' }}</p>
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="text-xs text-gray-500">{{ materia.professor?.name }}</span>
                            <div v-if="materia.plano_ensino_path" class="mt-1">
                                <a :href="`/storage/${materia.plano_ensino_path}`" target="_blank" class="text-xs text-indigo-600 hover:text-indigo-900">
                                    📄 Ver Plano de Ensino
                                </a>
                            </div>
                        </div>
                        <div class="flex space-x-2" v-if="user?.role === 'professor'">
                            <button @click.stop="editMateria(materia)" class="text-indigo-600 hover:text-indigo-900 text-sm">
                                Editar
                            </button>
                            <button @click.stop="deleteMateria(materia)" class="text-red-600 hover:text-red-900 text-sm">
                                Excluir
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <Modal :show="showCreateModal || showEditModal" :title="editingMateria ? 'Editar Matéria' : 'Nova Matéria'" @close="closeModal">
                <form @submit.prevent="saveMateria">
                    <div class="space-y-4">
                        <FormInput
                            id="nome"
                            v-model="form.nome"
                            label="Nome"
                            required
                            :error="errors.nome"
                        />
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Descrição</label>
                            <textarea
                                v-model="form.descricao"
                                rows="4"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            ></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Plano de Ensino (PDF)</label>
                            <input
                                type="file"
                                @change="handlePlanoChange"
                                accept=".pdf"
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                            />
                            <p v-if="materia?.plano_ensino_path" class="mt-1 text-sm text-gray-600">
                                <a :href="`/storage/${materia.plano_ensino_path}`" target="_blank" class="text-indigo-600 hover:text-indigo-900">
                                    Ver plano atual
                                </a>
                            </p>
                        </div>
                    </div>
                </form>
                <template #footer>
                    <Button variant="secondary" @click="closeModal" class="mr-3">
                        Cancelar
                    </Button>
                    <Button @click="saveMateria" :loading="loading">
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
const materias = ref([]);
const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingMateria = ref(null);
const loading = ref(false);
const errors = ref({});
const planoFile = ref(null);

const form = ref({
    nome: '',
    descricao: ''
});

const loadMaterias = async () => {
    try {
        const response = await axios.get('/materias');
        materias.value = response.data;
    } catch (error) {
        console.error('Erro ao carregar matérias:', error);
        if (error.response?.status === 401) {
            store.clearAuth();
            window.location.href = '/login';
        }
    }
};

const viewMateria = (materia) => {
    // Implementar visualização detalhada se necessário
};

const editMateria = (materia) => {
    editingMateria.value = materia;
    form.value = {
        nome: materia.nome,
        descricao: materia.descricao || ''
    };
    planoFile.value = null;
    showEditModal.value = true;
};

const closeModal = () => {
    showCreateModal.value = false;
    showEditModal.value = false;
    editingMateria.value = null;
    form.value = { nome: '', descricao: '' };
    errors.value = {};
    planoFile.value = null;
};

const handlePlanoChange = (event) => {
    planoFile.value = event.target.files[0];
};

const saveMateria = async () => {
    errors.value = {};
    loading.value = true;

    try {
        const formData = new FormData();
        formData.append('nome', form.value.nome);
        if (form.value.descricao) formData.append('descricao', form.value.descricao);
        if (planoFile.value) formData.append('plano_ensino', planoFile.value);

        if (editingMateria.value) {
            await axios.put(`/materias/${editingMateria.value.id}`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
        } else {
            await axios.post('/materias', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
        }
        await loadMaterias();
        closeModal();
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
        } else {
            alert('Erro ao salvar matéria');
        }
    } finally {
        loading.value = false;
    }
};

const deleteMateria = async (materia) => {
    if (!confirm('Tem certeza que deseja excluir esta matéria?')) return;

    try {
        await axios.delete(`/api/materias/${materia.id}`);
        await loadMaterias();
    } catch (error) {
        alert('Erro ao excluir matéria');
    }
};

onMounted(() => {
    loadMaterias();
});
</script>


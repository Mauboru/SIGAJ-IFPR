<template>
    <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Provas</h1>
                <Button @click="showCreateModal = true" v-if="user?.role === 'professor'">
                    Nova Prova
                </Button>
            </div>

            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <ul class="divide-y divide-gray-200">
                    <li v-for="prova in provas" :key="prova.id" class="px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">{{ prova.titulo }}</h3>
                                <p class="text-sm text-gray-600 mt-1" v-if="prova.observacoes">{{ prova.observacoes }}</p>
                                <div class="mt-2 flex items-center text-xs text-gray-500">
                                    <span>Data: {{ formatDate(prova.data) }}</span>
                                    <span class="mx-2">•</span>
                                    <span>Valor: {{ prova.valor_total }} pontos</span>
                                    <span class="mx-2">•</span>
                                    <span>{{ prova.turma?.nome }}</span>
                                </div>
                            </div>
                            <div class="flex space-x-2" v-if="user?.role === 'professor'">
                                <button @click="editProva(prova)" class="text-indigo-600 hover:text-indigo-900">
                                    Editar
                                </button>
                                <button @click="deleteProva(prova)" class="text-red-600 hover:text-red-900">
                                    Excluir
                                </button>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            <Modal :show="showCreateModal || showEditModal" :title="editingProva ? 'Editar Prova' : 'Nova Prova'" @close="closeModal">
                <form @submit.prevent="saveProva">
                    <div class="space-y-4">
                        <FormInput id="titulo" v-model="form.titulo" label="Título" required :error="errors.titulo" />
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Observações</label>
                            <textarea v-model="form.observacoes" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                        </div>
                        <FormInput id="data" v-model="form.data" type="date" label="Data" required :error="errors.data" />
                        <FormInput id="valor_total" v-model="form.valor_total" type="number" label="Valor Total" step="0.01" required :error="errors.valor_total" />
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Turma</label>
                            <select v-model="form.turma_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                <option value="">Selecione...</option>
                                <option v-for="turma in turmas" :key="turma.id" :value="turma.id">{{ turma.nome }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Matéria</label>
                            <select v-model="form.materia_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                <option value="">Selecione...</option>
                                <option v-for="materia in materias" :key="materia.id" :value="materia.id">{{ materia.nome }}</option>
                            </select>
                        </div>
                    </div>
                </form>
                <template #footer>
                    <Button variant="secondary" @click="closeModal" class="mr-3">Cancelar</Button>
                    <Button @click="saveProva" :loading="loading">Salvar</Button>
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
const provas = ref([]);
const turmas = ref([]);
const materias = ref([]);
const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingProva = ref(null);
const loading = ref(false);
const errors = ref({});

const form = ref({
    titulo: '',
    observacoes: '',
    data: '',
    valor_total: 10,
    turma_id: '',
    materia_id: ''
});

const formatDate = (date) => new Date(date).toLocaleDateString('pt-BR');

const loadProvas = async () => {
    try {
        const response = await axios.get('/provas');
        provas.value = response.data;
    } catch (error) {
        console.error('Erro ao carregar provas:', error);
    }
};

const loadTurmas = async () => {
    try {
        const response = await axios.get('/turmas');
        turmas.value = response.data;
    } catch (error) {
        console.error('Erro ao carregar turmas:', error);
    }
};

const loadMaterias = async () => {
    try {
        const response = await axios.get('/materias');
        materias.value = response.data;
    } catch (error) {
        console.error('Erro ao carregar matérias:', error);
    }
};

const editProva = (prova) => {
    editingProva.value = prova;
    form.value = {
        titulo: prova.titulo,
        observacoes: prova.observacoes || '',
        data: prova.data.split('T')[0],
        valor_total: prova.valor_total,
        turma_id: prova.turma_id,
        materia_id: prova.materia_id
    };
    showEditModal.value = true;
};

const closeModal = () => {
    showCreateModal.value = false;
    showEditModal.value = false;
    editingProva.value = null;
    form.value = { titulo: '', observacoes: '', data: '', valor_total: 10, turma_id: '', materia_id: '' };
    errors.value = {};
};

const saveProva = async () => {
    errors.value = {};
    loading.value = true;
    try {
        if (editingProva.value) {
            await axios.put(`/provas/${editingProva.value.id}`, form.value);
        } else {
            await axios.post('/provas', form.value);
        }
        await loadProvas();
        closeModal();
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
        } else {
            alert('Erro ao salvar prova');
        }
    } finally {
        loading.value = false;
    }
};

const deleteProva = async (prova) => {
    if (!confirm('Tem certeza que deseja excluir esta prova?')) return;
    try {
        await axios.delete(`/provas/${prova.id}`);
        await loadProvas();
    } catch (error) {
        alert('Erro ao excluir prova');
    }
};

onMounted(() => {
    loadProvas();
    if (user.value?.role === 'professor') {
        loadTurmas();
        loadMaterias();
    }
});
</script>


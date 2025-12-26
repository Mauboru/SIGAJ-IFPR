<template>
    <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Atividades</h1>
                <Button @click="showCreateModal = true" v-if="user?.role === 'professor'">
                    Nova Atividade
                </Button>
            </div>

            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <ul class="divide-y divide-gray-200">
                    <li v-for="atividade in atividades" :key="atividade.id" class="px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">{{ atividade.titulo }}</h3>
                                <p class="text-sm text-gray-600 mt-1">{{ atividade.descricao }}</p>
                                <div class="mt-2 flex items-center text-xs text-gray-500">
                                    <span>Entrega: {{ formatDate(atividade.data_entrega) }}</span>
                                    <span class="mx-2">•</span>
                                    <span>Valor: {{ atividade.valor_maximo }} pontos</span>
                                    <span class="mx-2">•</span>
                                    <span>{{ atividade.turma?.nome }}</span>
                                </div>
                            </div>
                            <div class="flex space-x-2" v-if="user?.role === 'professor'">
                                <button @click="editAtividade(atividade)" class="text-indigo-600 hover:text-indigo-900">
                                    Editar
                                </button>
                                <button @click="deleteAtividade(atividade)" class="text-red-600 hover:text-red-900">
                                    Excluir
                                </button>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            <Modal :show="showCreateModal || showEditModal" :title="editingAtividade ? 'Editar Atividade' : 'Nova Atividade'" @close="closeModal">
                <form @submit.prevent="saveAtividade">
                    <div class="space-y-4">
                        <FormInput
                            id="titulo"
                            v-model="form.titulo"
                            label="Título"
                            required
                            :error="errors.titulo"
                        />
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Descrição</label>
                            <textarea
                                v-model="form.descricao"
                                rows="4"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            ></textarea>
                        </div>
                        <FormInput
                            id="data_entrega"
                            v-model="form.data_entrega"
                            type="date"
                            label="Data de Entrega"
                            required
                            :error="errors.data_entrega"
                        />
                        <FormInput
                            id="valor_maximo"
                            v-model="form.valor_maximo"
                            type="number"
                            label="Valor Máximo"
                            step="0.01"
                            required
                            :error="errors.valor_maximo"
                        />
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
                    <Button @click="saveAtividade" :loading="loading">Salvar</Button>
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
const atividades = ref([]);
const turmas = ref([]);
const materias = ref([]);
const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingAtividade = ref(null);
const loading = ref(false);
const errors = ref({});

const form = ref({
    titulo: '',
    descricao: '',
    data_entrega: '',
    valor_maximo: 10,
    turma_id: '',
    materia_id: ''
});

const formatDate = (date) => new Date(date).toLocaleDateString('pt-BR');

const loadAtividades = async () => {
    try {
        const response = await axios.get('/atividades');
        atividades.value = response.data;
    } catch (error) {
        console.error('Erro ao carregar atividades:', error);
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

const editAtividade = (atividade) => {
    editingAtividade.value = atividade;
    form.value = {
        titulo: atividade.titulo,
        descricao: atividade.descricao || '',
        data_entrega: atividade.data_entrega.split('T')[0],
        valor_maximo: atividade.valor_maximo,
        turma_id: atividade.turma_id,
        materia_id: atividade.materia_id
    };
    showEditModal.value = true;
};

const closeModal = () => {
    showCreateModal.value = false;
    showEditModal.value = false;
    editingAtividade.value = null;
    form.value = { titulo: '', descricao: '', data_entrega: '', valor_maximo: 10, turma_id: '', materia_id: '' };
    errors.value = {};
};

const saveAtividade = async () => {
    errors.value = {};
    loading.value = true;
    try {
        if (editingAtividade.value) {
            await axios.put(`/atividades/${editingAtividade.value.id}`, form.value);
        } else {
            await axios.post('/atividades', form.value);
        }
        await loadAtividades();
        closeModal();
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
        } else {
            alert('Erro ao salvar atividade');
        }
    } finally {
        loading.value = false;
    }
};

const deleteAtividade = async (atividade) => {
    if (!confirm('Tem certeza que deseja excluir esta atividade?')) return;
    try {
        await axios.delete(`/atividades/${atividade.id}`);
        await loadAtividades();
    } catch (error) {
        alert('Erro ao excluir atividade');
    }
};

onMounted(() => {
    loadAtividades();
    if (user.value?.role === 'professor') {
        loadTurmas();
        loadMaterias();
    }
});
</script>


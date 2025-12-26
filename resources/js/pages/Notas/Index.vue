<template>
    <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Notas</h1>
                <Button @click="showCreateModal = true" v-if="user?.role === 'professor'">
                    Lançar Nota
                </Button>
            </div>

            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aluno</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Turma</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Atividade/Prova</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" v-if="user?.role === 'professor'">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="nota in notas" :key="nota.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ nota.aluno?.name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ nota.turma?.nome }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span v-if="nota.atividade">{{ nota.atividade.titulo }}</span>
                                <span v-if="nota.prova">{{ nota.prova.titulo }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ nota.valor }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" v-if="user?.role === 'professor'">
                                <button @click="editNota(nota)" class="text-indigo-600 hover:text-indigo-900 mr-4">Editar</button>
                                <button @click="deleteNota(nota)" class="text-red-600 hover:text-red-900">Excluir</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Modal :show="showCreateModal || showEditModal" :title="editingNota ? 'Editar Nota' : 'Lançar Nota'" @close="closeModal">
                <form @submit.prevent="saveNota">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Aluno</label>
                            <select v-model="form.aluno_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                <option value="">Selecione...</option>
                                <option v-for="aluno in alunos" :key="aluno.id" :value="aluno.id">{{ aluno.name }}</option>
                            </select>
                        </div>
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
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
                            <select v-model="notaTipo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                <option value="">Selecione...</option>
                                <option value="atividade">Atividade</option>
                                <option value="prova">Prova</option>
                            </select>
                        </div>
                        <div v-if="notaTipo === 'atividade'">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Atividade</label>
                            <select v-model="form.atividade_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                <option value="">Selecione...</option>
                                <option v-for="atividade in atividades" :key="atividade.id" :value="atividade.id">{{ atividade.titulo }}</option>
                            </select>
                        </div>
                        <div v-if="notaTipo === 'prova'">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Prova</label>
                            <select v-model="form.prova_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                <option value="">Selecione...</option>
                                <option v-for="prova in provas" :key="prova.id" :value="prova.id">{{ prova.titulo }}</option>
                            </select>
                        </div>
                        <FormInput id="valor" v-model="form.valor" type="number" label="Valor" step="0.01" required :error="errors.valor" />
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Observações</label>
                            <textarea v-model="form.observacoes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                        </div>
                    </div>
                </form>
                <template #footer>
                    <Button variant="secondary" @click="closeModal" class="mr-3">Cancelar</Button>
                    <Button @click="saveNota" :loading="loading">Salvar</Button>
                </template>
            </Modal>
        </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import store from '../../router/store';
import FormInput from '../../components/FormInput.vue';
import Button from '../../components/Button.vue';
import Modal from '../../components/Modal.vue';

const user = computed(() => store.getters.getUser());
const notas = ref([]);
const alunos = ref([]);
const turmas = ref([]);
const materias = ref([]);
const atividades = ref([]);
const provas = ref([]);
const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingNota = ref(null);
const loading = ref(false);
const errors = ref({});
const notaTipo = ref('');

const form = ref({
    aluno_id: '',
    turma_id: '',
    materia_id: '',
    atividade_id: null,
    prova_id: null,
    valor: 0,
    observacoes: ''
});

const loadNotas = async () => {
    try {
        const response = await axios.get('/notas');
        notas.value = response.data;
    } catch (error) {
        console.error('Erro ao carregar notas:', error);
    }
};

const loadAlunos = async () => {
    try {
        const response = await axios.get('/usuarios');
        alunos.value = response.data.filter(u => u.role === 'aluno');
    } catch (error) {
        console.error('Erro ao carregar alunos:', error);
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

const loadAtividades = async () => {
    try {
        const response = await axios.get('/atividades');
        atividades.value = response.data;
    } catch (error) {
        console.error('Erro ao carregar atividades:', error);
    }
};

const loadProvas = async () => {
    try {
        const response = await axios.get('/provas');
        provas.value = response.data;
    } catch (error) {
        console.error('Erro ao carregar provas:', error);
    }
};

watch(notaTipo, () => {
    form.value.atividade_id = null;
    form.value.prova_id = null;
});

const editNota = (nota) => {
    editingNota.value = nota;
    notaTipo.value = nota.atividade_id ? 'atividade' : 'prova';
    form.value = {
        aluno_id: nota.aluno_id,
        turma_id: nota.turma_id,
        materia_id: nota.materia_id,
        atividade_id: nota.atividade_id,
        prova_id: nota.prova_id,
        valor: nota.valor,
        observacoes: nota.observacoes || ''
    };
    showEditModal.value = true;
};

const closeModal = () => {
    showCreateModal.value = false;
    showEditModal.value = false;
    editingNota.value = null;
    notaTipo.value = '';
    form.value = { aluno_id: '', turma_id: '', materia_id: '', atividade_id: null, prova_id: null, valor: 0, observacoes: '' };
    errors.value = {};
};

const saveNota = async () => {
    errors.value = {};
    loading.value = true;
    try {
        const data = { ...form.value };
        if (notaTipo.value === 'atividade') {
            data.prova_id = null;
        } else {
            data.atividade_id = null;
        }
        if (editingNota.value) {
            await axios.put(`/notas/${editingNota.value.id}`, data);
        } else {
            await axios.post('/notas', data);
        }
        await loadNotas();
        closeModal();
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
        } else {
            alert('Erro ao salvar nota');
        }
    } finally {
        loading.value = false;
    }
};

const deleteNota = async (nota) => {
    if (!confirm('Tem certeza que deseja excluir esta nota?')) return;
    try {
        await axios.delete(`/notas/${nota.id}`);
        await loadNotas();
    } catch (error) {
        alert('Erro ao excluir nota');
    }
};

onMounted(() => {
    loadNotas();
    if (user.value?.role === 'professor') {
        loadAlunos();
        loadTurmas();
        loadMaterias();
        loadAtividades();
        loadProvas();
    }
});
</script>


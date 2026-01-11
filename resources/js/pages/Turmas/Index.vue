<template>
    <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Turmas</h1>
                <Button @click="showCreateModal = true" v-if="user?.role === 'professor'">
                    Nova Turma
                </Button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="turma in turmas"
                    :key="turma.id"
                    class="bg-white shadow rounded-lg p-6"
                >
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ turma.nome }}</h3>
                    <p class="text-sm text-gray-600 mb-2">
                        <span v-if="turma.materias && turma.materias.length > 0">
                            {{ turma.materias.map(m => m.nome).join(', ') }}
                        </span>
                        <span v-else class="text-gray-400">Sem matérias</span>
                    </p>
                    <p class="text-xs text-gray-500 mb-4">{{ turma.ano }} - {{ turma.semestre }}</p>
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-gray-500">{{ turma.alunos?.length || 0 }} alunos</span>
                        <div class="flex space-x-2" v-if="user?.role === 'professor'">
                            <button @click="editTurma(turma)" class="text-indigo-600 hover:text-indigo-900 text-sm">
                                Editar
                            </button>
                            <button @click="manageAlunos(turma)" class="text-blue-600 hover:text-blue-900 text-sm">
                                Alunos
                            </button>
                            <button @click="deleteTurma(turma)" class="text-red-600 hover:text-red-900 text-sm">
                                Excluir
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <Modal :show="showCreateModal || showEditModal" :title="editingTurma ? 'Editar Turma' : 'Nova Turma'" @close="closeModal">
                <form @submit.prevent="saveTurma">
                    <div class="space-y-4">
                        <FormInput
                            id="nome"
                            v-model="form.nome"
                            label="Nome"
                            required
                            :error="errors.nome"
                        />
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Matéria</label>
                            <select
                                v-model="form.materia_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                required
                            >
                                <option value="">Selecione...</option>
                                <option v-for="materia in materias" :key="materia.id" :value="materia.id">
                                    {{ materia.nome }}
                                </option>
                            </select>
                            <p v-if="errors.materia_id" class="mt-1 text-sm text-red-600">{{ errors.materia_id }}</p>
                        </div>
                        <FormInput
                            id="ano"
                            v-model="form.ano"
                            label="Ano"
                            required
                            :error="errors.ano"
                        />
                        <FormInput
                            id="semestre"
                            v-model="form.semestre"
                            label="Semestre"
                            required
                            :error="errors.semestre"
                        />
                    </div>
                </form>
                <template #footer>
                    <Button variant="secondary" @click="closeModal" class="mr-3">
                        Cancelar
                    </Button>
                    <Button @click="saveTurma" :loading="loading">
                        Salvar
                    </Button>
                </template>
            </Modal>

            <Modal :show="showAlunosModal" title="Gerenciar Alunos" @close="showAlunosModal = false">
                <div v-if="selectedTurma">
                    <h3 class="text-lg font-semibold mb-4">{{ selectedTurma.nome }}</h3>
                    
                    <div class="mb-6">
                        <h4 class="font-medium mb-2">Alunos na Turma</h4>
                        <div class="space-y-2">
                            <div v-for="aluno in alunosNaTurma" :key="aluno.id" class="flex justify-between items-center p-2 bg-gray-50 rounded">
                                <span>{{ aluno.name }}</span>
                                <button @click="removerAluno(aluno.id)" class="text-red-600 hover:text-red-900 text-sm">
                                    Remover
                                </button>
                            </div>
                            <p v-if="alunosNaTurma.length === 0" class="text-sm text-gray-500">Nenhum aluno na turma</p>
                        </div>
                    </div>

                    <div>
                        <h4 class="font-medium mb-2">Adicionar Alunos</h4>
                        <div class="space-y-2">
                            <div v-for="aluno in alunosDisponiveis" :key="aluno.id" class="flex justify-between items-center p-2 bg-gray-50 rounded">
                                <span>{{ aluno.name }}</span>
                                <button @click="adicionarAluno(aluno.id)" class="text-indigo-600 hover:text-indigo-900 text-sm">
                                    Adicionar
                                </button>
                            </div>
                            <p v-if="alunosDisponiveis.length === 0" class="text-sm text-gray-500">Todos os alunos já estão na turma</p>
                        </div>
                    </div>
                </div>
                <template #footer>
                    <Button @click="showAlunosModal = false">Fechar</Button>
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
const turmas = ref([]);
const materias = ref([]);
const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingTurma = ref(null);
const loading = ref(false);
const errors = ref({});
const showAlunosModal = ref(false);
const selectedTurma = ref(null);
const alunosDisponiveis = ref([]);
const alunosNaTurma = ref([]);

const form = ref({
    nome: '',
    materia_id: '',
    ano: '',
    semestre: ''
});

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

const editTurma = (turma) => {
    editingTurma.value = turma;
    form.value = {
        nome: turma.nome,
        materia_id: turma.materia_id,
        ano: turma.ano,
        semestre: turma.semestre
    };
    showEditModal.value = true;
};

const manageAlunos = async (turma) => {
    selectedTurma.value = turma;
    
    // Recarregar turma com alunos
    try {
        const response = await axios.get(`/turmas/${turma.id}`);
        alunosNaTurma.value = response.data.alunos || [];
        
        // Carregar todos os alunos disponíveis
        const alunosResponse = await axios.get('/usuarios?role=aluno');
        alunosDisponiveis.value = alunosResponse.data.filter(a => 
            !alunosNaTurma.value.some(t => t.id === a.id)
        );
    } catch (error) {
        console.error('Erro ao carregar alunos:', error);
    }
    
    showAlunosModal.value = true;
};

const adicionarAluno = async (alunoId) => {
    try {
        await axios.post(`/turmas/${selectedTurma.value.id}/alunos`, {
            aluno_ids: [alunoId]
        });
        await manageAlunos(selectedTurma.value);
        await loadTurmas();
    } catch (error) {
        alert('Erro ao adicionar aluno');
    }
};

const removerAluno = async (alunoId) => {
    if (!confirm('Tem certeza que deseja remover este aluno da turma?')) return;
    
    try {
        await axios.delete(`/turmas/${selectedTurma.value.id}/alunos/${alunoId}`);
        await manageAlunos(selectedTurma.value);
        await loadTurmas();
    } catch (error) {
        alert('Erro ao remover aluno');
    }
};

const closeModal = () => {
    showCreateModal.value = false;
    showEditModal.value = false;
    editingTurma.value = null;
    form.value = { nome: '', materia_id: '', ano: '', semestre: '' };
    errors.value = {};
};

const saveTurma = async () => {
    errors.value = {};
    loading.value = true;

    try {
        if (editingTurma.value) {
            await axios.put(`/turmas/${editingTurma.value.id}`, form.value);
        } else {
            await axios.post('/turmas', form.value);
        }
        await loadTurmas();
        closeModal();
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
        } else {
            alert('Erro ao salvar turma');
        }
    } finally {
        loading.value = false;
    }
};

const deleteTurma = async (turma) => {
    if (!confirm('Tem certeza que deseja excluir esta turma?')) return;

    try {
        await axios.delete(`/turmas/${turma.id}`);
        await loadTurmas();
    } catch (error) {
        alert('Erro ao excluir turma');
    }
};

onMounted(() => {
    loadTurmas();
    if (user.value?.role === 'professor') {
        loadMaterias();
    }
});
</script>


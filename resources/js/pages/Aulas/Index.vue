<template>
    <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Aulas</h1>
                <Button @click="showCreateModal = true" v-if="user?.role === 'professor'">
                    Nova Aula
                </Button>
            </div>

            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <ul class="divide-y divide-gray-200">
                    <li v-for="aula in aulas" :key="aula.id" class="px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">{{ aula.titulo }}</h3>
                                <p class="text-sm text-gray-600 mt-1">{{ aula.descricao }}</p>
                                <div class="mt-2 flex items-center text-xs text-gray-500">
                                    <span>{{ formatDate(aula.data) }}</span>
                                    <span class="mx-2">•</span>
                                    <span>{{ aula.turma?.nome }}</span>
                                    <span class="mx-2">•</span>
                                    <span>{{ aula.materia?.nome }}</span>
                                </div>
                            </div>
                            <div class="flex space-x-2" v-if="user?.role === 'professor'">
                                <button @click="editAula(aula)" class="text-indigo-600 hover:text-indigo-900">
                                    Editar
                                </button>
                                <button @click="uploadArquivo(aula)" class="text-blue-600 hover:text-blue-900">
                                    Arquivos
                                </button>
                                <button @click="deleteAula(aula)" class="text-red-600 hover:text-red-900">
                                    Excluir
                                </button>
                            </div>
                        </div>
                        <div v-if="aula.arquivos && aula.arquivos.length > 0" class="mt-2">
                            <div class="text-xs text-gray-500">
                                Arquivos:
                                <span v-for="(arquivo, idx) in aula.arquivos" :key="arquivo.id" class="ml-2">
                                    <a :href="`/arquivos/${arquivo.id}/download`" target="_blank" class="text-indigo-600 hover:text-indigo-900">
                                        {{ arquivo.nome_original }}
                                    </a>
                                    <span v-if="idx < aula.arquivos.length - 1">,</span>
                                </span>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            <Modal :show="showCreateModal || showEditModal" :title="editingAula ? 'Editar Aula' : 'Nova Aula'" @close="closeModal">
                <form @submit.prevent="saveAula">
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
                            id="data"
                            v-model="form.data"
                            type="date"
                            label="Data"
                            required
                            :error="errors.data"
                        />
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Turma</label>
                            <select
                                v-model="form.turma_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                required
                            >
                                <option value="">Selecione...</option>
                                <option v-for="turma in turmas" :key="turma.id" :value="turma.id">
                                    {{ turma.nome }}
                                </option>
                            </select>
                        </div>
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
                        </div>
                    </div>
                </form>
                <template #footer>
                    <Button variant="secondary" @click="closeModal" class="mr-3">
                        Cancelar
                    </Button>
                    <Button @click="saveAula" :loading="loading">
                        Salvar
                    </Button>
                </template>
            </Modal>

            <Modal :show="showArquivoModal" title="Upload de Arquivo" @close="showArquivoModal = false">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Arquivo PDF</label>
                        <input
                            type="file"
                            @change="handleArquivoChange"
                            accept=".pdf"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                        />
                    </div>
                </div>
                <template #footer>
                    <Button variant="secondary" @click="showArquivoModal = false" class="mr-3">
                        Cancelar
                    </Button>
                    <Button @click="saveArquivo">
                        Enviar
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
const aulas = ref([]);
const turmas = ref([]);
const materias = ref([]);
const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingAula = ref(null);
const loading = ref(false);
const errors = ref({});
const showArquivoModal = ref(false);
const selectedAula = ref(null);
const arquivoFile = ref(null);

const uploadArquivo = (aula) => {
    selectedAula.value = aula;
    showArquivoModal.value = true;
};

const handleArquivoChange = (event) => {
    arquivoFile.value = event.target.files[0];
};

const saveArquivo = async () => {
    if (!arquivoFile.value) {
        alert('Selecione um arquivo');
        return;
    }

    const formData = new FormData();
    formData.append('arquivo', arquivoFile.value);

    try {
        await axios.post(`/aulas/${selectedAula.value.id}/arquivos`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        await loadAulas();
        showArquivoModal.value = false;
        arquivoFile.value = null;
    } catch (error) {
        alert('Erro ao fazer upload do arquivo');
    }
};

const form = ref({
    titulo: '',
    descricao: '',
    data: '',
    turma_id: '',
    materia_id: ''
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('pt-BR');
};

const loadAulas = async () => {
    try {
        const response = await axios.get('/aulas');
        aulas.value = response.data.map(aula => ({
            ...aula,
            arquivos: aula.arquivos || []
        }));
    } catch (error) {
        console.error('Erro ao carregar aulas:', error);
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

const editAula = (aula) => {
    editingAula.value = aula;
    form.value = {
        titulo: aula.titulo,
        descricao: aula.descricao || '',
        data: aula.data.split('T')[0],
        turma_id: aula.turma_id,
        materia_id: aula.materia_id
    };
    showEditModal.value = true;
};

const closeModal = () => {
    showCreateModal.value = false;
    showEditModal.value = false;
    editingAula.value = null;
    form.value = { titulo: '', descricao: '', data: '', turma_id: '', materia_id: '' };
    errors.value = {};
};

const saveAula = async () => {
    errors.value = {};
    loading.value = true;

    try {
        if (editingAula.value) {
            await axios.put(`/aulas/${editingAula.value.id}`, form.value);
        } else {
            await axios.post('/aulas', form.value);
        }
        await loadAulas();
        closeModal();
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
        } else {
            alert('Erro ao salvar aula');
        }
    } finally {
        loading.value = false;
    }
};

const deleteAula = async (aula) => {
    if (!confirm('Tem certeza que deseja excluir esta aula?')) return;

    try {
        await axios.delete(`/aulas/${aula.id}`);
        await loadAulas();
    } catch (error) {
        alert('Erro ao excluir aula');
    }
};

onMounted(() => {
    loadAulas();
    if (user.value?.role === 'professor') {
        loadTurmas();
        loadMaterias();
    }
});
</script>


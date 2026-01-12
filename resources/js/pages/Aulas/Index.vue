<template>
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Aulas</h1>
            <Button @click="showCreateModal = true" v-if="user?.role === 'professor'">
                Nova Aula
            </Button>
        </div>

        <!-- Grid de Cards -->
        <div v-if="aulas.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
                v-for="aula in aulas"
                :key="aula.id"
                class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden transform transition-all duration-200 hover:shadow-lg hover:scale-[1.02] hover:border-indigo-300 dark:hover:border-indigo-600 group"
            >
                <!-- Header com gradiente sutil -->
                <div class="bg-gradient-to-r from-blue-500 to-indigo-600 h-2"></div>
                
                <div class="p-6">
                    <!-- Título da Aula -->
                    <div class="flex items-start justify-between mb-3">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors pr-2 flex-1">
                            {{ aula.titulo }}
                        </h3>
                    </div>

                    <!-- Descrição -->
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 dark:text-gray-300 line-clamp-2" v-if="aula.descricao">
                            {{ aula.descricao }}
                        </p>
                        <p class="text-sm text-gray-400 dark:text-gray-500 italic" v-else>
                            Sem descrição
                        </p>
                    </div>

                    <!-- Informações adicionais -->
                    <div class="space-y-2 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <!-- Data -->
                        <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                            <svg class="w-4 h-4 mr-2 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="font-medium">{{ formatDate(aula.data) }}</span>
                        </div>

                        <!-- Turma -->
                        <div class="flex items-center text-sm text-gray-600 dark:text-gray-400" v-if="aula.turma">
                            <svg class="w-4 h-4 mr-2 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span class="font-medium">{{ aula.turma.nome }}</span>
                        </div>

                        <!-- Matéria -->
                        <div class="flex items-center text-sm text-gray-600 dark:text-gray-400" v-if="aula.materia">
                            <svg class="w-4 h-4 mr-2 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            <span class="font-medium">{{ aula.materia.nome }}</span>
                        </div>

                        <!-- Arquivos -->
                        <div class="flex items-center text-sm text-gray-600 dark:text-gray-400" v-if="aula.arquivos && aula.arquivos.length > 0">
                            <svg class="w-4 h-4 mr-2 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <span class="font-medium">{{ aula.arquivos.length }} arquivo{{ aula.arquivos.length !== 1 ? 's' : '' }}</span>
                        </div>
                    </div>

                    <!-- Ações (apenas para professores) -->
                    <div v-if="user?.role === 'professor'" class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 flex space-x-2">
                        <button 
                            @click.stop="editAula(aula)" 
                            class="flex-1 px-3 py-2 text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-md transition-colors"
                        >
                            Editar
                        </button>
                        <button 
                            @click.stop="uploadArquivo(aula)" 
                            class="flex-1 px-3 py-2 text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-md transition-colors"
                        >
                            Arquivos
                        </button>
                        <button 
                            @click.stop="deleteAula(aula)" 
                            class="flex-1 px-3 py-2 text-sm font-medium text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md transition-colors"
                        >
                            Excluir
                        </button>
                    </div>

                    <!-- Lista de arquivos (se houver) -->
                    <div v-if="aula.arquivos && aula.arquivos.length > 0" class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">Arquivos disponíveis:</p>
                        <div class="space-y-1">
                            <button
                                v-for="arquivo in aula.arquivos" 
                                :key="arquivo.id"
                                @click="downloadArquivo(arquivo)"
                                class="w-full flex items-center text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 transition-colors text-left"
                            >
                                <svg class="w-3 h-3 mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                <span class="truncate">{{ arquivo.nome_original }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estado vazio -->
        <div v-else class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Nenhuma aula cadastrada</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                <span v-if="user?.role === 'professor'">Comece criando uma nova aula.</span>
                <span v-else>Não há aulas disponíveis no momento.</span>
            </p>
            <div v-if="user?.role === 'professor'" class="mt-6">
                <Button @click="showCreateModal = true">
                    Nova Aula
                </Button>
            </div>
        </div>

        <!-- Modal de Cadastro/Edição -->
        <Modal :show="showCreateModal || showEditModal" :title="editingAula ? 'Editar Aula' : 'Nova Aula'" @close="closeModal" size="large">
            <form @submit.prevent="saveAula">
                <div class="space-y-6">
                    <FormInput
                        id="titulo"
                        v-model="form.titulo"
                        label="Título da Aula"
                        placeholder="Ex: Introdução à Programação Web"
                        required
                        :error="errors.titulo"
                    />
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Descrição
                        </label>
                        <textarea
                            v-model="form.descricao"
                            rows="4"
                            placeholder="Descreva o conteúdo da aula..."
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-indigo-500 dark:focus:ring-indigo-400 sm:text-sm px-3 py-2 transition-colors"
                            :class="errors.descricao ? 'border-red-300 dark:border-red-600 focus:border-red-500 dark:focus:border-red-400 focus:ring-red-500 dark:focus:ring-red-400' : ''"
                        ></textarea>
                        <p v-if="errors.descricao" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.descricao }}</p>
                    </div>

                    <FormInput
                        id="data"
                        v-model="form.data"
                        type="date"
                        label="Data da Aula"
                        required
                        :error="errors.data"
                    />

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Turma <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="form.turma_id"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-indigo-500 dark:focus:ring-indigo-400 sm:text-sm px-3 py-2 transition-colors"
                            :class="errors.turma_id ? 'border-red-300 dark:border-red-600 focus:border-red-500 dark:focus:border-red-400 focus:ring-red-500 dark:focus:ring-red-400' : ''"
                            required
                        >
                            <option value="">Selecione uma turma...</option>
                            <option v-for="turma in turmas" :key="turma.id" :value="turma.id">
                                {{ turma.nome }} - {{ turma.ano }}/{{ turma.semestre }}
                            </option>
                        </select>
                        <p v-if="errors.turma_id" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.turma_id }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Matéria <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="form.materia_id"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-indigo-500 dark:focus:ring-indigo-400 sm:text-sm px-3 py-2 transition-colors"
                            :class="errors.materia_id ? 'border-red-300 dark:border-red-600 focus:border-red-500 dark:focus:border-red-400 focus:ring-red-500 dark:focus:ring-red-400' : ''"
                            required
                        >
                            <option value="">Selecione uma matéria...</option>
                            <option v-for="materia in materias" :key="materia.id" :value="materia.id">
                                {{ materia.nome }}
                            </option>
                        </select>
                        <p v-if="errors.materia_id" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.materia_id }}</p>
                    </div>
                </div>
            </form>
            <template #footer>
                <Button variant="secondary" @click="closeModal" class="mr-3">
                    Cancelar
                </Button>
                <Button @click="saveAula" :loading="loading">
                    {{ editingAula ? 'Atualizar' : 'Salvar' }}
                </Button>
            </template>
        </Modal>

        <!-- Modal de Upload de Arquivo -->
        <Modal :show="showArquivoModal" title="Upload de Arquivo" @close="closeArquivoModal">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Arquivo PDF
                    </label>
                    <input
                        type="file"
                        @change="handleArquivoChange"
                        accept=".pdf"
                        class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 dark:file:bg-indigo-900/30 file:text-indigo-700 dark:file:text-indigo-300 hover:file:bg-indigo-100 dark:hover:file:bg-indigo-900/50 transition-colors"
                    />
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                        Tamanho máximo: 10MB. Apenas arquivos PDF são permitidos.
                    </p>
                </div>

                <!-- Aula selecionada -->
                <div v-if="selectedAula" class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Aula:</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ selectedAula.titulo }}</p>
                </div>
            </div>
            <template #footer>
                <Button variant="secondary" @click="closeArquivoModal" class="mr-3">
                    Cancelar
                </Button>
                <Button @click="saveArquivo" :loading="loadingArquivo" :disabled="!arquivoFile">
                    Enviar Arquivo
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
const loadingArquivo = ref(false);

const uploadArquivo = (aula) => {
    selectedAula.value = aula;
    arquivoFile.value = null;
    showArquivoModal.value = true;
};

const closeArquivoModal = () => {
    showArquivoModal.value = false;
    selectedAula.value = null;
    arquivoFile.value = null;
};

const handleArquivoChange = (event) => {
    arquivoFile.value = event.target.files[0];
};

const saveArquivo = async () => {
    if (!arquivoFile.value) {
        alert('Selecione um arquivo');
        return;
    }

    loadingArquivo.value = true;
    const formData = new FormData();
    formData.append('arquivo', arquivoFile.value);

    try {
        await axios.post(`/aulas/${selectedAula.value.id}/arquivos`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        await loadAulas();
        closeArquivoModal();
    } catch (error) {
        if (error.response?.status === 422) {
            alert(error.response.data.message || 'Erro ao fazer upload do arquivo');
        } else {
            alert('Erro ao fazer upload do arquivo');
        }
    } finally {
        loadingArquivo.value = false;
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
    if (!date) return '';
    return new Date(date).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
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
        if (error.response?.status === 401) {
            store.clearAuth();
            window.location.href = '/login';
        }
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
        turma_id: aula.turma_id || '',
        materia_id: aula.materia_id || ''
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
            const errorData = error.response.data.errors || {};
            errors.value = {};
            Object.keys(errorData).forEach(key => {
                errors.value[key] = Array.isArray(errorData[key]) ? errorData[key][0] : errorData[key];
            });
        } else {
            alert('Erro ao salvar aula: ' + (error.response?.data?.message || error.message));
        }
    } finally {
        loading.value = false;
    }
};

const deleteAula = async (aula) => {
    if (!confirm(`Tem certeza que deseja excluir a aula "${aula.titulo}"?`)) return;

    try {
        await axios.delete(`/aulas/${aula.id}`);
        await loadAulas();
    } catch (error) {
        alert('Erro ao excluir aula: ' + (error.response?.data?.message || error.message));
    }
};

const downloadArquivo = async (arquivo) => {
    try {
        const response = await axios.get(`/arquivos/${arquivo.id}/download`, {
            responseType: 'blob'
        });
        
        // Criar link temporário para download
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', arquivo.nome_original);
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
    } catch (error) {
        alert('Erro ao baixar arquivo: ' + (error.response?.data?.message || error.message));
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

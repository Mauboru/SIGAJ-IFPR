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
                    class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden cursor-pointer transform transition-all duration-200 hover:shadow-lg hover:scale-[1.02] hover:border-indigo-300 dark:hover:border-indigo-600 group"
                    @click="viewMateria(materia)"
                >
                    <!-- Header com gradiente sutil -->
                    <div class="bg-gradient-to-r from-indigo-500 to-purple-600 h-2"></div>
                    
                    <div class="p-6">
                        <!-- Título da Matéria -->
                        <div class="flex items-start justify-between mb-3">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors pr-2">
                                {{ materia.nome }}
                            </h3>
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-indigo-500 dark:group-hover:text-indigo-400 transition-colors flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>

                        <!-- Descrição -->
                        <div class="mb-4">
                            <p class="text-sm text-gray-600 dark:text-gray-300 line-clamp-2" v-if="materia.descricao">
                                {{ stripHtml(materia.descricao) }}
                            </p>
                            <p class="text-sm text-gray-400 dark:text-gray-500 italic" v-else>
                                Sem descrição
                            </p>
                        </div>

                        <!-- Informações adicionais -->
                        <div class="space-y-2 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <!-- Professor -->
                            <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span class="font-medium">{{ materia.professor?.name || 'Sem professor' }}</span>
                            </div>

                            <!-- Aulas -->
                            <div class="flex items-center text-sm text-gray-600 dark:text-gray-400" v-if="materia.aulas && materia.aulas.length > 0">
                                <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                <span class="font-medium">{{ materia.aulas.length }} aula{{ materia.aulas.length !== 1 ? 's' : '' }} cadastrada{{ materia.aulas.length !== 1 ? 's' : '' }}</span>
                            </div>

                            <!-- Turmas -->
                            <div class="flex items-center text-sm text-gray-600 dark:text-gray-400" v-if="materia.turmas && materia.turmas.length > 0">
                                <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span class="font-medium">{{ materia.turmas.length }} turma{{ materia.turmas.length !== 1 ? 's' : '' }} vinculada{{ materia.turmas.length !== 1 ? 's' : '' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <Modal :show="showCreateModal || showEditModal" :title="editingMateria ? 'Editar Matéria' : 'Nova Matéria'" @close="closeModal" size="large">
                <form @submit.prevent="saveMateria">
                    <div class="space-y-6">
                        <!-- Nome da Matéria -->
                        <FormInput
                            id="nome"
                            v-model="form.nome"
                            label="Nome da Matéria"
                            placeholder="Ex: Programação Web"
                            required
                            :error="errors.nome"
                        />

                        <!-- Seletor Múltiplo de Turmas -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Turmas Atreladas <span class="text-gray-500 text-xs">(opcional)</span>
                            </label>
                            <div class="mt-1 space-y-2 max-h-48 overflow-y-auto border border-gray-300 dark:border-gray-600 rounded-md p-2 bg-white dark:bg-gray-700">
                                <div v-if="turmasDisponiveis.length === 0" class="text-sm text-gray-500 text-center py-2">
                                    Nenhuma turma disponível
                                </div>
                                <label
                                    v-for="turma in turmasDisponiveis"
                                    :key="turma.id"
                                    class="flex items-center p-2 hover:bg-gray-100 dark:hover:bg-gray-600 rounded cursor-pointer"
                                >
                                    <input
                                        type="checkbox"
                                        :value="turma.id"
                                        v-model="form.turma_ids"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700"
                                    />
                                    <span class="ml-2 text-sm text-gray-900 dark:text-gray-100">
                                        {{ turma.nome }} - {{ turma.ano }}/{{ turma.semestre }}
                                    </span>
                                </label>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                Selecione uma ou mais turmas para atrelar a esta matéria ({{ form.turma_ids.length }} selecionada{{ form.turma_ids.length !== 1 ? 's' : '' }})
                            </p>
                        </div>

                        <!-- Descrição com Editor Rico -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Descrição
                            </label>
                            <RichTextEditor
                                v-model="form.descricao"
                                placeholder="Descreva a matéria aqui... Você pode usar formatação rica de texto."
                            />
                        </div>

                        <!-- Plano de Ensino - Aulas -->
                        <div class="border-t pt-6">
                            <div class="flex justify-between items-center mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Plano de Ensino (Aulas)
                                    </label>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Total de aulas: <span class="font-semibold">{{ form.aulas.length }}</span>
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    @click="adicionarAula"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-medium transition-colors"
                                >
                                    + Adicionar Aula
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mb-4">Adicione as aulas do plano de ensino (data e tema)</p>
                            
                            <!-- Formulário para adicionar nova aula -->
                            <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-lg p-4 mb-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Data da Aula
                                        </label>
                                        <input
                                            v-model="novaAula.data"
                                            type="date"
                                            class="w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-indigo-500 dark:focus:ring-indigo-400 sm:text-sm px-3 py-2"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Tema da Aula
                                        </label>
                                        <div class="flex gap-2">
                                            <input
                                                v-model="novaAula.titulo"
                                                type="text"
                                                placeholder="Ex: Introdução à Programação Web"
                                                @keyup.enter="adicionarAula"
                                                class="flex-1 rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-indigo-500 dark:focus:ring-indigo-400 sm:text-sm px-3 py-2"
                                            />
                                            <button
                                                type="button"
                                                @click="adicionarAula"
                                                :disabled="!novaAula.data || !novaAula.titulo"
                                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-sm font-medium transition-colors"
                                            >
                                                Adicionar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Lista de Aulas Cadastradas -->
                            <div v-if="form.aulas && form.aulas.length > 0" class="space-y-2">
                                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">#</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Data</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tema</th>
                                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                            <tr
                                                v-for="(aula, index) in form.aulas"
                                                :key="index"
                                                class="hover:bg-gray-50 dark:hover:bg-gray-700"
                                            >
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                    {{ index + 1 }}
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                    {{ formatarData(aula.data) }}
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                                    {{ aula.titulo }}
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                                    <button
                                                        type="button"
                                                        @click="removerAula(index)"
                                                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                                        title="Remover aula"
                                                    >
                                                        Remover
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div v-else class="text-center py-8 text-gray-500 text-sm border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg">
                                Nenhuma aula adicionada. Use o formulário acima para adicionar aulas.
                            </div>
                        </div>

                        <!-- Upload de PDFs Extras (opcional) -->
                        <div class="border-t pt-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Arquivos Extras (PDFs) <span class="text-gray-500 text-xs">(opcional)</span>
                            </label>
                            <input
                                type="file"
                                @change="handleArquivosExtrasChange"
                                accept=".pdf"
                                multiple
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 dark:file:bg-indigo-900 file:text-indigo-700 dark:file:text-indigo-300 hover:file:bg-indigo-100 dark:hover:file:bg-indigo-800"
                            />
                            <p class="mt-2 text-xs text-gray-500">
                                Você pode selecionar múltiplos arquivos PDF para upload
                            </p>
                            
                            <!-- Lista de arquivos selecionados -->
                            <div v-if="form.arquivos_extras.length > 0" class="mt-3 space-y-2">
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Arquivos selecionados ({{ form.arquivos_extras.length }}):
                                </p>
                                <div class="space-y-1">
                                    <div
                                        v-for="(arquivo, index) in form.arquivos_extras"
                                        :key="index"
                                        class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded text-sm"
                                    >
                                        <span class="text-gray-900 dark:text-gray-100">{{ arquivo.name }}</span>
                                        <button
                                            type="button"
                                            @click="removerArquivoExtra(index)"
                                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 text-xs"
                                        >
                                            Remover
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Arquivos existentes (ao editar) -->
                            <div v-if="editingMateria?.arquivos?.length > 0" class="mt-4">
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Arquivos existentes:
                                </p>
                                <div class="space-y-1">
                                    <div
                                        v-for="arquivo in editingMateria.arquivos"
                                        :key="arquivo.id"
                                        class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded text-sm"
                                    >
                                        <a
                                            :href="`/storage/${arquivo.caminho}`"
                                            target="_blank"
                                            class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300"
                                        >
                                            📄 {{ arquivo.nome_original }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <template #footer>
                    <Button variant="secondary" @click="closeModal" class="mr-3">
                        Cancelar
                    </Button>
                    <Button @click="saveMateria" :loading="loading">
                        Salvar Matéria
                    </Button>
                </template>
            </Modal>

            <!-- Modal de Detalhes da Matéria -->
            <Modal :show="showDetailsModal" title="Detalhes da Matéria" @close="showDetailsModal = false" size="large">
                <div v-if="selectedMateria" class="space-y-6">
                    <!-- Informações Gerais -->
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ selectedMateria.nome }}</h2>
                        <p class="text-sm text-gray-500 mb-4">
                            Professor: <span class="font-medium">{{ selectedMateria.professor?.name }}</span>
                        </p>
                        <div v-if="selectedMateria.descricao" class="prose max-w-none" v-html="selectedMateria.descricao"></div>
                        <div v-else class="text-sm text-gray-500 italic">Sem descrição</div>
                    </div>

                    <!-- Turmas -->
                    <div v-if="selectedMateria.turmas && selectedMateria.turmas.length > 0">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Turmas</h3>
                        <div class="flex flex-wrap gap-2">
                            <span
                                v-for="turma in selectedMateria.turmas"
                                :key="turma.id"
                                class="px-3 py-1 bg-indigo-100 text-indigo-800 rounded-full text-sm"
                            >
                                {{ turma.nome }} - {{ turma.ano }}/{{ turma.semestre }}
                            </span>
                        </div>
                    </div>

                    <!-- Aulas do Plano de Ensino -->
                    <div>
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">
                                Aulas do Plano de Ensino
                            </h3>
                            <span class="text-sm text-gray-500">
                                Total: {{ selectedMateria.aulas?.length || 0 }} aula{{ (selectedMateria.aulas?.length || 0) !== 1 ? 's' : '' }}
                            </span>
                        </div>
                        
                        <div v-if="selectedMateria.aulas && selectedMateria.aulas.length > 0">
                            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">#</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Data</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tema da Aula</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        <tr
                                            v-for="(aula, index) in sortedAulas"
                                            :key="aula.id || index"
                                            class="hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer"
                                            @click="viewAula(aula)"
                                        >
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                {{ index + 1 }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                {{ formatarData(aula.data) }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                                {{ aula.titulo }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 text-gray-500 text-sm border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg">
                            Nenhuma aula cadastrada
                        </div>
                    </div>
                </div>
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
import RichTextEditor from '../../components/RichTextEditor.vue';

const user = computed(() => store.getters.getUser());
const materias = ref([]);
const turmasDisponiveis = ref([]);
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDetailsModal = ref(false);
const selectedMateria = ref(null);
const editingMateria = ref(null);
const loading = ref(false);
const errors = ref({});

const form = ref({
    nome: '',
    descricao: '',
    turma_ids: [],
    aulas: [],
    arquivos_extras: []
});

const novaAula = ref({
    data: '',
    titulo: ''
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

const loadTurmas = async () => {
    try {
        const response = await axios.get('/turmas');
        turmasDisponiveis.value = response.data;
    } catch (error) {
        console.error('Erro ao carregar turmas:', error);
    }
};

const viewMateria = async (materia) => {
    // Carregar matéria completa com aulas
    try {
        const response = await axios.get(`/materias/${materia.id}`);
        selectedMateria.value = response.data;
        showDetailsModal.value = true;
    } catch (error) {
        console.error('Erro ao carregar detalhes da matéria:', error);
        alert('Erro ao carregar detalhes da matéria');
    }
};

const editMateria = async (materia) => {
    editingMateria.value = materia;
    
    // Carregar matéria completa com aulas e turmas
    try {
        const response = await axios.get(`/materias/${materia.id}`);
        const materiaCompleta = response.data;
        
        form.value = {
            nome: materiaCompleta.nome,
            descricao: materiaCompleta.descricao || '',
            turma_ids: materiaCompleta.turmas?.map(t => t.id) || [],
            aulas: materiaCompleta.aulas?.map(aula => ({
                titulo: aula.titulo,
                data: aula.data
            })) || [],
            arquivos_extras: []
        };
    } catch (error) {
        form.value = {
            nome: materia.nome,
            descricao: materia.descricao || '',
            turma_ids: [],
            aulas: [],
            arquivos_extras: []
        };
    }
    
    novaAula.value = { data: '', titulo: '' };
    showEditModal.value = true;
};

const closeModal = () => {
    showCreateModal.value = false;
    showEditModal.value = false;
    showDetailsModal.value = false;
    selectedMateria.value = null;
    editingMateria.value = null;
    form.value = { 
        nome: '', 
        descricao: '', 
        turma_ids: [], 
        aulas: [],
        arquivos_extras: []
    };
    novaAula.value = { data: '', titulo: '' };
    errors.value = {};
};

const adicionarAula = () => {
    if (!novaAula.value.data || !novaAula.value.titulo) {
        return;
    }
    
    if (!form.value.aulas) {
        form.value.aulas = [];
    }
    
    // Verificar se já existe aula com a mesma data
    const aulaExistente = form.value.aulas.find(a => a.data === novaAula.value.data);
    if (aulaExistente) {
        if (!confirm('Já existe uma aula nesta data. Deseja substituir?')) {
            return;
        }
        const index = form.value.aulas.indexOf(aulaExistente);
        form.value.aulas[index] = {
            titulo: novaAula.value.titulo,
            data: novaAula.value.data
        };
    } else {
        form.value.aulas.push({
            titulo: novaAula.value.titulo,
            data: novaAula.value.data
        });
    }
    
    // Ordenar aulas por data
    form.value.aulas.sort((a, b) => new Date(a.data) - new Date(b.data));
    
    // Limpar formulário
    novaAula.value = { data: '', titulo: '' };
};

const removerAula = (index) => {
    if (form.value.aulas && form.value.aulas.length > index) {
        form.value.aulas.splice(index, 1);
    }
};

const handleArquivosExtrasChange = (event) => {
    const files = Array.from(event.target.files);
    form.value.arquivos_extras = [...form.value.arquivos_extras, ...files];
};

const removerArquivoExtra = (index) => {
    form.value.arquivos_extras.splice(index, 1);
};

const formatarData = (data) => {
    if (!data) return '';
    const date = new Date(data);
    return date.toLocaleDateString('pt-BR');
};

const stripHtml = (html) => {
    if (!html) return '';
    const tmp = document.createElement('DIV');
    tmp.innerHTML = html;
    return tmp.textContent || tmp.innerText || '';
};

const sortedAulas = computed(() => {
    if (!selectedMateria.value?.aulas) return [];
    return [...selectedMateria.value.aulas].sort((a, b) => {
        return new Date(a.data) - new Date(b.data);
    });
});

const viewAula = (aula) => {
    // Por enquanto apenas um placeholder, pode ser implementado depois
    console.log('Visualizar aula:', aula);
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
        
        if (form.value.descricao) {
            formData.append('descricao', form.value.descricao);
        }
        
        // Adicionar múltiplas turmas
        if (form.value.turma_ids && form.value.turma_ids.length > 0) {
            form.value.turma_ids.forEach((id) => {
                formData.append('turma_ids[]', id);
            });
        }
        
        // Adicionar aulas do plano de ensino
        if (form.value.aulas && form.value.aulas.length > 0) {
            formData.append('aulas', JSON.stringify(form.value.aulas));
        }
        
        // Adicionar arquivos extras
        if (form.value.arquivos_extras && form.value.arquivos_extras.length > 0) {
            form.value.arquivos_extras.forEach((arquivo, index) => {
                formData.append(`arquivos_extras[${index}]`, arquivo);
            });
        }

        // Para FormData, não definir Content-Type - o navegador faz isso automaticamente com boundary
        if (editingMateria.value) {
            // Para edição, ainda usar o método antigo (aulas serão gerenciadas separadamente)
            await axios.put(`/materias/${editingMateria.value.id}`, formData);
        } else {
            await axios.post('/materias', formData);
        }
        
        await loadMaterias();
        closeModal();
    } catch (error) {
        console.error('Erro ao salvar matéria:', error);
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
        } else {
            alert('Erro ao salvar matéria: ' + (error.response?.data?.message || error.message));
        }
    } finally {
        loading.value = false;
    }
};

const deleteMateria = async (materia) => {
    if (!confirm('Tem certeza que deseja excluir esta matéria?')) return;

    try {
        await axios.delete(`/materias/${materia.id}`);
        await loadMaterias();
    } catch (error) {
        alert('Erro ao excluir matéria');
    }
};

onMounted(() => {
    loadMaterias();
    if (user.value?.role === 'professor') {
        loadTurmas();
    }
});
</script>


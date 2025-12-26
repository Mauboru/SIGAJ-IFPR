<script setup>
import { ref, reactive, watch, onMounted } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'

const loadingEPIs = ref(false);
const loadingFicha = ref(false);
const colaboradores = ref([])
const funcaoNome = ref('')
const setorNome = ref('')
const epis = ref([])
const form = reactive({
  colaboradorId: null,
  dataEmissao: new Date().toISOString().substr(0, 10),
  dataAdmissao: '',
  matricula: '',
  funcao: '',
  setor: '',
  observacoes: '',
  periodoEntrega: { dtInicial: '', dtFinal: '' }
})

watch(() => form.colaboradorId, (newId) => {
  const colab = colaboradores.value.find(c => String(c.CODIGO) === String(newId))
  if (colab) {
    form.dataAdmissao = colab.DATA_ADMISSAO ? colab.DATA_ADMISSAO.substring(0,10) : ''
    form.matricula = colab.N_CARTEIRA || ''

    form.funcao = colab.COD_FUNCAO || ''       
    form.setor = colab.COD_DEPARTAMENTO || ''  

    funcaoNome.value = colab.FUNCAO || ''     
    setorNome.value = colab.DEPARTAMENTO || ''
  } else {
    form.dataAdmissao = ''
    form.matricula = ''
    form.funcao = ''
    form.setor = ''
    funcaoNome.value = ''
    setorNome.value = ''
  }
}, { immediate: true })

const carregarColaboradores = async () => {
  try {
    const response = await axios.get(`${import.meta.env.VITE_URL_SITE}/api/funcionario/index`);
    colaboradores.value = response.data;
  } catch (error) {
    Swal.fire('Erro', 'Falha ao carregar colaboradores', 'error')
  }
}

const carregarEPIs = async () => {
  if (!form.colaboradorId || !form.periodoEntrega.dtInicial || !form.periodoEntrega.dtFinal) {
    Swal.fire('Erro', 'Selecione colaborador e período de entrega', 'error');
    return;
  }
  loadingEPIs.value = true

  try {
    const response = await axios.get(`${import.meta.env.VITE_URL_SITE}/api/epi/index`, {
      params: {
        COD_FUNCIONARIO: form.colaboradorId,
        DATA_INICIAL: form.periodoEntrega.dtInicial,
        DATA_FINAL: form.periodoEntrega.dtFinal
      }
    })

    console.log(response)

    epis.value = response.data.map(e => ({
      id: e.CODIGO,
      nome: e.DESCRICAO_PRODUTO,
      quantidade: e.QUANTIDADE,
      entrega: e.DATA_ENTREGA,
      validade: 0
    }))
  } catch (error) {
    Swal.fire('Erro', error.response.data.message, 'error')
  } finally {
    loadingEPIs.value = false
  }
}

const salvarFicha = async () => {
  loadingFicha.value = true
  try {
    const response = await axios.post(
      `${import.meta.env.VITE_URL_SITE}/api/ficha_epi/create`, {
        colaboradorId: form.colaboradorId,
        funcionarioId: 1, 
        dataEmissao: form.dataEmissao,
        dataAdmissao: form.dataAdmissao,
        matricula: form.matricula,
        funcao: form.funcao,
        setor: form.setor,
        observacoes: form.observacoes,
        periodoEntrega: {
          dtInicial: form.periodoEntrega.dtInicial,
          dtFinal: form.periodoEntrega.dtFinal
        }
      }, { headers: { 'Content-Type': 'application/json' } }
    )
    Swal.fire('Sucesso', response.data.message, 'success');
    cleanFicha();
  } catch (error) {
    Swal.fire('Erro', error.response?.data?.message || error.message, 'error')
  } finally {
    loadingFicha.value = false
  }
}

const convertDate = (date) => {
  if (!date) return "";
  const [year, month, day] = date.split("-");
  return `${day}/${month}/${year}`;
};

const cleanFicha = () => {
  form.colaboradorId = null;
  form.dataEmissao = new Date().toISOString().substr(0, 10);
  form.dataAdmissao = '';
  form.matricula = '';
  form.funcao = '';
  form.setor = '';
  form.observacoes = '';
  form.periodoEntrega = { dtInicial: '', dtFinal: '' };
  epis.value = [];
};

onMounted(() => {
  carregarColaboradores()
})
</script>

<template>
  <div class="home-container">
    <div class="page-header">
      <div class="page-titles">
        <div class="breadcrumb">
          <span class="page-title-text">Fichas de EPI</span>
          <span class="pipe"> | </span>
          <span class="breadcrumb-icon" aria-hidden="true">
            <VIcon size="18" icon="tabler-clipboard-list" />
          </span>
          <span class="sep"> › </span>
          <span class="crumb-current">Cadastro</span>
        </div>
      </div>
    </div>
    <div class="content">
      <!-- Formulário -->
      <div class="form-section">
        <div class="form-group">
          <label>Colaborador</label>
          <select v-model="form.colaboradorId">
            <option value="" disabled>Selecione um colaborador</option>
            <option 
              v-for="colab in colaboradores" 
              :key="colab.CODIGO" 
              :value="String(colab.CODIGO)"
            >
              {{ colab.NOME }}
            </option>
          </select>
        </div>

        <div class="form-group two-columns">
          <div class="field">
            <label>Data de Emissão</label>
            <input type="date" v-model="form.dataEmissao">
          </div>
          <div class="field">
            <label>Data de Admissão</label>
            <input type="date" v-model="form.dataAdmissao" disabled>
          </div>
        </div>

        <div class="form-group">
          <label>Matrícula</label>
          <input type="text" v-model="form.matricula" disabled>
        </div>

        <div class="form-group two-columns">
          <div class="field">
            <label>Função</label>
            <input type="text" v-model="funcaoNome" disabled>
          </div>
          <div class="field">
            <label>Setor / Departamento</label>
            <input type="text" v-model="setorNome" disabled>
          </div>
        </div>

        <div class="form-group">
          <label>Observações</label>
          <textarea v-model="form.observacoes" placeholder="Opcional"></textarea>
        </div>

        <label>Período de Entrega</label>
        <div class="form-group two-columns">
          <div class="field">
            <input 
              type="date" 
              v-model="form.periodoEntrega.dtInicial" 
              :max="form.periodoEntrega.dtFinal || null"
            >
          </div>
          até
          <div class="field">
            <input 
              type="date" 
              v-model="form.periodoEntrega.dtFinal" 
              :min="form.periodoEntrega.dtInicial || null"
            >
          </div>
        </div>

        <div class="form-group two-columns">
          <div class="field">
            <button @click="carregarEPIs" :disabled="loadingEPIs">
              {{ loadingEPIs ? 'Carregando...' : 'Carregar EPIs' }}
            </button>
          </div>
          <div class="field">
            <button class="save-button" @click="salvarFicha" :disabled="loadingFicha || !epis.length">
              {{ loadingFicha ? 'Salvando...' : 'Salvar Ficha' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Tabela EPIs -->
      <div class="table-section">
        <h2>TABELA EPIs</h2>
        <table v-if="epis.length">
          <thead>
            <tr>
              <th>Nome</th>
              <th>Qtd</th>
              <th>Entrega</th>
              <th>Vencimento</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="e in epis" :key="e.id">
              <td>{{ e.nome }}</td>
              <td>{{ e.quantidade }}</td>
              <td>{{ convertDate(e.entrega) }}</td>
              <td>{{ convertDate(e.validade) }}</td>
            </tr>
          </tbody>
        </table>
        <p v-else>Nenhum EPI carregado.</p>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Page header */
.page-header { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1rem; }
.breadcrumb { font-size: 0.875rem; color: #7f8c8d; display: flex; align-items: center; gap: 0.5rem; }
.breadcrumb .page-title-text { font-weight: 700; color: #2c3e50; }
.breadcrumb .pipe { margin: 0 0.5rem; color: #b0b8bf; }
.breadcrumb .sep { margin: 0 0.25rem; }
.breadcrumb-icon { display: inline-flex; align-items: center; justify-content: center; line-height: 1; color: #90a4ae; }
/* Botão salvar */
.save-button {
  padding: 12px;
  font-size: 16px;
  width: 100%;
  background-color: #27ae60;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: background-color 0.2s;
}

.save-button:disabled {
  background-color: #95a5a6;
  cursor: not-allowed;
}

.save-button:hover:not(:disabled) {
  background-color: #1e8449;
}

/* Layout e colunas */
.form-group.two-columns {
  display: flex;
  flex-direction: row; 
  gap: 10px;
}

.form-group.two-columns .field {
  flex: 1;
  display: flex;
  flex-direction: column;
}

/* Container geral */
.home-container {
  padding: 20px;
  max-width: 1200px;
  margin: auto;
  font-family: Arial, sans-serif;
}

h1 {
  text-align: center;
  color: #2c3e50;
  margin-bottom: 20px;
}

.content {
  display: flex;
  gap: 40px;
  flex-wrap: wrap;
}

/* Seções */
.form-section,
.table-section {
  flex: 1;
  min-width: 350px;
  padding: 20px;
  border-radius: 10px;
  display: flex;
  flex-direction: column;
  gap: 15px;
  box-shadow: 0 0 10px rgba(0,0,0,0.05);
}

/* Fundo claro padrão */
.form-section,
.table-section {
  background-color: #f9f9f9;
}

/* Labels */
label {
  font-weight: 600;
  font-size: 14px;
  color: #34495e;
}

/* Inputs, selects e textarea padrão */
input, select, textarea {
  padding: 10px;
  font-size: 14px;
  width: 100%;
  box-sizing: border-box;
  border: 1px solid #ccc;
  border-radius: 6px;
  background-color: #fff;
  color: #333;
  transition: border 0.2s, background-color 0.2s, color 0.2s;
}

input:focus, select:focus, textarea:focus {
  border-color: #3498db;
  outline: none;
}

textarea {
  min-height: 80px;
  resize: vertical;
}

/* Botões padrão */
button {
  padding: 12px;
  font-size: 16px;
  width: 100%;
  background-color: #3498db;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: background-color 0.2s;
}

button:disabled {
  background-color: #95a5a6;
  cursor: not-allowed;
}

button:hover:not(:disabled) {
  background-color: #2980b9;
}

/* Inputs de período */
.periodo-inputs {
  display: flex;
  align-items: center;
  gap: 10px;
}

.periodo-inputs span {
  font-size: 14px;
}

/* Tabela */
.table-section table {
  width: 100%;
  border-collapse: collapse;
}

.table-section th,
.table-section td {
  text-align: center; 
  vertical-align: middle; 
  padding: 8px;
  border: 1px solid #ccc;
}

.table-section th {
  background-color: #888888;
  color: #fff;
}

.table-section td {
  background-color: #fff;
  color: #333;
}

.table-section h2 {
  text-align: center; 
}

/* Select do colaborador específico */
.form-section > .form-group:first-child select {
  background-color: #fff;
  color: #333;
  border: 1px solid #ccc;
}

/* Responsivo */
@media (max-width: 768px) {
  .content {
    flex-direction: column;
  }

  .form-section,
  .table-section {
    min-width: auto;
    width: 100%;
  }

  .form-group.two-columns {
    flex-direction: column;
  }
}

/* ==========================
   MODO ESCURO
=========================== */
.v-theme--dark .form-section,
.v-theme--dark .table-section {
  background-color: #2e344c; /* fundo escuro principal */
  box-shadow: 0 0 15px rgba(0,0,0,0.4);
}

/* Labels */
.v-theme--dark label {
  color: #eceff4; /* título claro */
}

/* Inputs, selects e textarea */
.v-theme--dark input,
.v-theme--dark select,
.v-theme--dark textarea {
  background-color: #3b4252; /* fundo escuro */
  color: #eceff4; /* texto claro */
  border: 1px solid #4c566a;
}

.v-theme--dark input:focus,
.v-theme--dark select:focus,
.v-theme--dark textarea:focus {
  border-color: #81a1c1; /* destaque de foco */
}

/* Select do colaborador específico */
.v-theme--dark .form-section > .form-group:first-child select {
  background-color: #3b4252;
  color: #eceff4;
  border: 1px solid #4c566a;
}

/* Tabela */
.v-theme--dark .table-section th {
  background-color: #434c6e;
  color: #eceff4;
}

.v-theme--dark .table-section td {
  background-color: #2e344c;
  color: #eceff4;
}

/* Hover das linhas da tabela */
.v-theme--dark .table-section tbody tr:nth-child(even) {
  background-color: #2a2a2a;
}

.v-theme--dark .table-section tbody tr:nth-child(odd) {
  background-color: #2e344c;
}

.v-theme--dark .table-section tbody tr:hover {
  background-color: #434c6e;
}

/* Botões */
.v-theme--dark button {
  background-color: #81a1c1;
  color: #2e344c;
}

.v-theme--dark button:hover:not(:disabled) {
  background-color: #5e81ac;
}

.v-theme--dark .save-button {
  background-color: #a3be8c;
}

.v-theme--dark .save-button:hover:not(:disabled) {
  background-color: #8fbcbb;
}

.v-theme--dark button:disabled,
.v-theme--dark .save-button:disabled {
  background-color: #4c566a;
  color: #888;
  cursor: not-allowed;
}

</style>

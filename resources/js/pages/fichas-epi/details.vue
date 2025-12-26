<script setup>
import { ref, onMounted, nextTick } from "vue";
import { useRoute } from "vue-router";
import { FingerprintSpinner } from "epic-spinners";
import SignaturePad from "signature_pad";
import axios from "axios";
import Swal from "sweetalert2";

const route = useRoute();
const id = ref(route.params.id);
const loading = ref(true);
const ficha = ref(null);
const epis = ref([]);

const canvasRef = ref(null);

const modalAberto = ref(false);
const codigoAssinar = ref(null);
const signaturePad = ref(null);

const selecionados = ref([]);

const carregarDetalhes = async () => {
  loading.value = true;
  try {
    const response = await axios.get(
      `${import.meta.env.VITE_URL_SITE}/api/ficha_epi/show/${id.value}`
    );
    ficha.value = response.data.ficha;
    epis.value = response.data.epis;
    console.log(response);
  } catch (error) {
    Swal.fire(
      "Erro",
      error.response?.data?.message || "Falha ao carregar detalhes",
      "error"
    );
  } finally {
    loading.value = false;
  }
};

const converterReal = (valor) => {
  return new Intl.NumberFormat("pt-BR", {
    style: "currency",
    currency: "BRL",
  }).format(valor);
};

const inserirDataDevolucao = async (codigo, data) => {
  try {
    const response = await axios.put(
      `${import.meta.env.VITE_URL_SITE}/api/assinatura/update`,
      { codigo, data }
    );
    Swal.fire("Successo", response.data.message, "success");
    carregarDetalhes(); // Recarrega para atualizar a interface
  } catch (error) {
    Swal.fire(
      "Erro",
      error.response?.data?.message || "Falha ao inserir data",
      "error"
    );
  }
};

const fecharModal = () => {
  modalAberto.value = false;
  signaturePad.value = null;
};

const convertDate = (date) => {
  if (!date) return "-";
  const [year, month, day] = date.split("-");
  return `${day}/${month}/${year}`;
};

const toggleSelecionarTodos = () => {
  const naoAssinados = epis.value
    .filter((e) => !e.ASSINADO)
    .map((e) => e.CODIGO);
  if (naoAssinados.every((codigo) => selecionados.value.includes(codigo))) {
    selecionados.value = [];
  } else {
    selecionados.value = naoAssinados;
  }
};

const abrirAssinaturaSelecionados = () => {
  if (!selecionados.value.length) {
    Swal.fire("Aviso", "Selecione ao menos um EPI para assinar", "info");
    return;
  }
  codigoAssinar.value = null;
  modalAberto.value = true;
  nextTick(() => {
    signaturePad.value = new SignaturePad(canvasRef.value);
  });
};

const salvarAssinaturaSelecionados = async () => {
  if (!selecionados.value.length) return;
  loading.value = true;
  const dataUrl = signaturePad.value.toDataURL("image/png");
  fecharModal();

  try {
    // Sempre usar assinarEntregaEpi, funciona para um ou múltiplos itens
    await axios.post(
      `${import.meta.env.VITE_URL_SITE}/api/assinatura/entrega-epi`,
      {
        codigos: selecionados.value,
        assinatura: dataUrl,
        id_ficha: id.value,
      }
    );

    Swal.fire("Sucesso", "Assinatura aplicada com sucesso", "success");
    selecionados.value = [];
    carregarDetalhes();
  } catch (error) {
    console.error(error);
    Swal.fire(
      "Erro",
      error.response?.data?.message || "Falha ao salvar assinaturas",
      "error"
    );
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  carregarDetalhes();
});
</script>

<template>
  <div v-if="loading" class="spinner">
    <fingerprint-spinner
      :animation-duration="1500"
      :size="64"
      color="#2c3e50" />
  </div>

  <div v-else class="content-wrapper">
    <div class="page-header">
      <div class="page-titles">
        <div class="breadcrumb">
          <span class="page-title-text">Fichas de EPI</span>
          <span class="pipe"> | </span>
          <span class="breadcrumb-icon" aria-hidden="true">
            <VIcon size="18" icon="tabler-clipboard-list" />
          </span>
          <span class="sep"> › </span>
          <span class="crumb-current">Detalhes</span>
        </div>
      </div>
    </div>

    <h2 class="title">Detalhes da Ficha</h2>

    <section class="ficha-info">
      <div class="grid">
        <div class="field colaborador">
          <label>Colaborador</label>
          <input type="text" :value="ficha.funcionario.NOME" disabled />
        </div>
        <div class="field matricula">
          <label>Matrícula</label>
          <input type="text" :value="ficha.MATRICULA" disabled />
        </div>
        <div class="field funcao">
          <label>Função</label>
          <input
            type="text"
            :value="ficha.funcao?.FUNCAO || 'Sem Função'"
            disabled />
        </div>
        <div class="field setor">
          <label>Setor</label>
          <input
            type="text"
            :value="ficha.departamento?.DEPARTAMENTO || 'Sem Departamento'"
            disabled />
        </div>

        <div class="date-wrapper">
          <div class="date-row">
            <div class="field">
              <label>Data de Emissão</label>
              <input
                type="text"
                :value="convertDate(ficha.DATA_EMISSAO)"
                disabled />
            </div>
            <div class="field">
              <label>Data de Admissão</label>
              <input
                type="text"
                :value="convertDate(ficha.DATA_ADMISSAO)"
                disabled />
            </div>
          </div>
          <div class="field periodo">
            <label>Período de Entrega</label>
            <input
              type="text"
              :value="`${convertDate(
                ficha.DATA_INICIAL_ENTREGA
              )} até ${convertDate(ficha.DATA_FINAL_ENTREGA)}`"
              disabled />
          </div>
        </div>

        <div class="field observacoes">
          <label>Observações</label>
          <textarea :value="ficha.OBS || 'Nenhuma'" disabled></textarea>
        </div>
      </div>
    </section>

    <section class="epis-info">
      <div class="epis-header">
        <div class="mobile-select-all">
          <label>
            <input
              type="checkbox"
              :checked="
                selecionados.length &&
                selecionados.length === epis.filter((e) => !e.ASSINADO).length
              "
              @change="toggleSelecionarTodos" />
            Selecionar Todos
          </label>
        </div>

        <h3>EPIs Entregues</h3>
        <button
          class="btn btn-save"
          :disabled="selecionados.length === 0"
          @click="abrirAssinaturaSelecionados">
          Assinar Selecionados
        </button>
      </div>

      <!-- versão desktop -->
      <div v-if="epis.length" class="table-responsive desktop-only">
        <table>
          <thead>
            <tr>
              <th>
                <input
                  type="checkbox"
                  :checked="
                    selecionados.length ===
                    epis.filter((e) => !e.ASSINADO).length
                  "
                  @change="toggleSelecionarTodos" />
              </th>
              <th>ASSINADO</th>
              <th>DATA DEVOLUÇÃO</th>
              <th>DESCRIÇÃO</th>
              <th>VALOR</th>
              <th>QUANTIDADE</th>
              <th>SUBTOTAL</th>
              <th>DATA ENTREGA</th>
              <th>DATA VENCIMENTO</th>
              <th>C.A</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="e in epis" :key="e.id">
              <td>
                <input
                  type="checkbox"
                  :value="e.CODIGO"
                  v-model="selecionados"
                  :disabled="e.ASSINADO" />
              </td>

              <td>
                <a v-if="e.ASSINADO" :href="e.ASSINADO" target="_blank">
                  <img :src="e.ASSINADO" alt="Assinatura" />
                </a>
                <span v-else>❌</span>
              </td>

              <td style="min-width: 150px; text-align: center">
                <div>
                  <template v-if="e.DATA_DEVOLUCAO">{{
                    convertDate(e.DATA_DEVOLUCAO)
                  }}</template>
                  <template v-else>
                    <input
                      type="date"
                      v-model="e.novaData"
                      @change="inserirDataDevolucao(e.CODIGO, e.novaData)"
                      style="width: 100%" />
                  </template>
                </div>
              </td>

              <td>{{ e.NOME }}</td>
              <td>{{ converterReal(e.VALOR) }}</td>
              <td>{{ e.QTD }}</td>
              <td>{{ converterReal(e.SUBTOTAL) }}</td>
              <td>{{ convertDate(e.DATA_ENTREGA) }}</td>
              <td>{{ convertDate(e.DATA_VENCIMENTO) }}</td>
              <td>{{ e.NUM_CA ?? "-" }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- versão mobile -->
      <div v-if="epis.length" class="mobile-cards">
        <div v-for="e in epis" :key="e.id" class="epi-card">
          <div class="card-header">
            <div class="card-select">
              <input
                v-if="!e.ASSINADO"
                type="checkbox"
                :value="e.CODIGO"
                v-model="selecionados" />
              <div v-else class="status-icon">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="20"
                  height="20"
                  fill="#27ae60"
                  viewBox="0 0 24 24">
                  <path
                    d="M20.285 6.708l-11.285 11.292-5.285-5.292 1.414-1.414 3.871 3.879 9.871-9.879z" />
                </svg>
              </div>
            </div>
            <div class="card-header-info">
              <h4 class="epi-name">{{ e.NOME }}</h4>
              <span class="epi-ca">CA: {{ e.NUM_CA || "-" }}</span>
            </div>
          </div>

          <div class="card-body">
            <div class="info-grid">
              <div class="info-item">
                <span class="info-label">Valor:</span>
                <span class="info-value">{{ converterReal(e.VALOR) }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Qtd:</span>
                <span class="info-value">{{ e.QTD }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Subtotal:</span>
                <span class="info-value">{{ converterReal(e.SUBTOTAL) }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Entrega:</span>
                <span class="info-value">{{
                  convertDate(e.DATA_ENTREGA)
                }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Vencimento:</span>
                <span class="info-value">{{
                  convertDate(e.DATA_VENCIMENTO)
                }}</span>
              </div>
              <div class="info-item full-width">
                <span class="info-label">Devolução:</span>
                <span class="info-value">
                  <template v-if="e.DATA_DEVOLUCAO">
                    {{ convertDate(e.DATA_DEVOLUCAO) }}
                  </template>
                  <template v-else>
                    <input
                      type="date"
                      v-model="e.novaData"
                      @change="inserirDataDevolucao(e.CODIGO, e.novaData)"
                      class="date-input" />
                  </template>
                </span>
              </div>
              <div class="info-item full-width">
                <span class="info-label">Assinatura:</span>
                <span class="info-value">
                  <a
                    v-if="e.ASSINADO"
                    :href="e.ASSINADO"
                    target="_blank"
                    class="signature-link">
                    ✅ Ver assinatura
                  </a>
                  <span v-else class="not-signed">❌ Não assinado</span>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <p v-else class="empty-msg">Nenhum EPI entregue nesta ficha.</p>
    </section>

    <!-- Modal de Assinatura -->
    <div v-if="modalAberto" class="modal">
      <div class="modal-content">
        <h3>Assinatura</h3>
        <canvas
          ref="canvasRef"
          width="400"
          height="200"
          class="signature-canvas"></canvas>
        <div class="modal-buttons">
          <button @click="salvarAssinaturaSelecionados" class="btn btn-save">
            Salvar
          </button>
          <button @click="fecharModal" class="btn btn-cancel">Cancelar</button>
          <button @click="signaturePad.clear()" class="btn btn-clear">
            Limpar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 1rem;
}
.breadcrumb {
  font-size: 0.875rem;
  color: #7f8c8d;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
.breadcrumb .page-title-text {
  font-weight: 700;
  color: #2c3e50;
}
.breadcrumb .pipe {
  margin: 0 0.5rem;
  color: #b0b8bf;
}
.breadcrumb .sep {
  margin: 0 0.25rem;
}
.breadcrumb-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  line-height: 1;
  color: #90a4ae;
}
/* ===== Botão Selecionar Todos no Mobile ===== */
.mobile-select-all {
  display: none;
  margin-bottom: 1rem;
  font-weight: 600;
  align-items: center;
  gap: 0.5rem;
}

.mobile-select-all input {
  width: 18px;
  height: 18px;
}

@media (max-width: 768px) {
  .mobile-select-all {
    display: flex;
  }
}

/* ===== MOBILE CARDS OTIMIZADOS ===== */
.mobile-cards {
  display: none;
  grid-template-columns: 1fr;
  gap: 1rem;
  padding-top: 1rem;
}

.epi-card {
  background: #ffffff;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  padding: 1rem;
  display: flex;
  flex-direction: column;
}

.card-header {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  margin-bottom: 0.75rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid #f0f0f0;
}

.card-select {
  flex-shrink: 0;
  margin-top: 0.25rem;
}

.card-select input[type="checkbox"] {
  width: 18px;
  height: 18px;
}

.status-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 20px;
  height: 20px;
}

.card-header-info {
  flex: 1;
  min-width: 0;
}

.epi-name {
  font-size: 1rem;
  font-weight: 600;
  color: #2c3e50;
  margin: 0 0 0.25rem 0;
  line-height: 1.2;
  word-wrap: break-word;
}

.epi-ca {
  font-size: 0.85rem;
  color: #7f8c8d;
  font-weight: 500;
}

.info-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 0.5rem;
}

.info-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.25rem 0;
  font-size: 0.85rem;
  line-height: 1.3;
}

.info-item.full-width {
  flex-direction: column;
  align-items: stretch;
  gap: 0.25rem;
}

.info-label {
  font-weight: 600;
  color: #555;
  flex-shrink: 0;
  margin-right: 0.5rem;
}

.info-value {
  color: #2c3e50;
  text-align: right;
  word-break: break-word;
  flex: 1;
  min-width: 0;
}

.info-item.full-width .info-value {
  text-align: left;
}

.date-input {
  width: 100%;
  padding: 0.4rem;
  border: 1px solid #ccc;
  border-radius: 4px;
  font-size: 0.85rem;
}

.signature-link {
  color: #27ae60;
  text-decoration: none;
  font-weight: 500;
}

.signature-link:hover {
  text-decoration: underline;
}

.not-signed {
  color: #e74c3c;
  font-weight: 500;
}

/* Ajustes para telas muito pequenas */
@media (max-width: 380px) {
  .epi-card {
    padding: 0.75rem;
  }

  .info-item {
    font-size: 0.8rem;
  }

  .epi-name {
    font-size: 0.9rem;
  }
}

/* ===== RESPONSIVIDADE ===== */
@media (max-width: 768px) {
  .desktop-only {
    display: none;
  }
  .mobile-cards {
    display: grid;
  }

  .content-wrapper {
    padding: 1rem;
  }

  .epis-header {
    flex-direction: column;
    gap: 1rem;
    align-items: stretch;
  }

  .epis-header h3 {
    margin: 0;
    text-align: center;
  }
}

@media (min-width: 769px) {
  .mobile-cards {
    display: none;
  }
  .desktop-only {
    display: block;
  }
}

/* ===== RESTANTE DO CSS PERMANECE IGUAL ===== */
.spinner {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 70vh;
}

.content-wrapper {
  background: #ffffff;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
  width: 100%;
  box-sizing: border-box;
}

@media (max-width: 767px) {
  .content-wrapper {
    padding: 1rem;
    margin: 0.5rem;
  }
}

.title {
  font-size: 1.8rem;
  margin-bottom: 1.5rem;
  color: #2c3e50;
}

/* Ficha info */
/* Ficha info - LAYOUT CORRIGIDO */
.ficha-info .grid {
  display: grid;
  grid-template-columns: 1fr;
  grid-gap: 1rem;
  grid-template-areas:
    "colaborador"
    "matricula"
    "funcao"
    "setor"
    "date-wrapper"
    "observacoes";
}

@media (min-width: 768px) {
  .ficha-info .grid {
    grid-template-columns: repeat(4, 1fr);
    grid-template-areas:
      "colaborador matricula funcao setor"
      "date-wrapper date-wrapper observacoes observacoes";
  }
}

.date-wrapper {
  grid-area: date-wrapper;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

@media (min-width: 768px) {
  .date-wrapper {
    flex-direction: row;
  }
}

.date-row {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

@media (min-width: 768px) {
  .date-row {
    flex-direction: row;
  }
}

.date-row > .field {
  flex: 1;
}

/* Garantir que os campos não estourem */
.field input,
.field textarea {
  max-width: 100%;
  box-sizing: border-box;
}

.colaborador {
  grid-area: colaborador;
}
.matricula {
  grid-area: matricula;
}
.funcao {
  grid-area: funcao;
}
.setor {
  grid-area: setor;
}

.date-wrapper {
  grid-area: date-wrapper;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.date-row {
  display: flex;
  gap: 1rem;
}

.date-row > .field {
  flex: 1;
}

.observacoes {
  grid-area: observacoes;
  display: flex;
  flex-direction: column;
}

.observacoes textarea {
  flex-grow: 1;
  padding: 0.5rem 0.8rem;
  border: 1px solid #ccc;
  border-radius: 6px;
  background-color: #f9f9f9;
  color: #333;
  resize: none;
}

.field {
  display: flex;
  flex-direction: column;
}

.field label {
  font-weight: 600;
  margin-bottom: 0.3rem;
  color: #555;
}

.field input,
.field textarea {
  padding: 0.5rem 0.8rem;
  border: 1px solid #ccc;
  border-radius: 6px;
  background-color: #f9f9f9;
  color: #333;
}

/* Epis info */
.epis-info {
  margin-top: 2rem;
}

.epis-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.btn {
  padding: 0.5rem 1rem;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  border: none;
  transition: background 0.2s, color 0.2s;
}

.btn-save {
  background-color: #2ecc71;
  color: #fff;
}
.btn-save:hover:enabled {
  background-color: #27ae60;
}

.btn-save:disabled {
  background-color: #ccc;
  color: #666;
  cursor: not-allowed;
}

.table-responsive {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
  min-width: 400px;
  table-layout: fixed;
}

table img {
  max-width: 100%;
  max-height: 80px;
  width: auto;
  height: auto;
  object-fit: contain;
  display: block;
  margin: 0 auto;
}

th,
td {
  text-align: center;
  border: 1px solid #ddd;
  padding: 0.8rem 1rem;
  vertical-align: middle;
  word-wrap: break-word;
}

tbody tr:nth-child(even) {
  background-color: #f9f9f9;
}

tbody tr:hover {
  background-color: #eef1f5;
}

.empty-msg {
  color: #888;
  font-style: italic;
  margin-top: 1rem;
}

/* Modal */
.modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

.modal-content {
  background: #fff;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
  max-width: 500px;
  width: 90%;
  text-align: center;
}

.modal-content h3 {
  margin-bottom: 1rem;
  color: #2c3e50;
}

.signature-canvas {
  border: 1px solid #ccc;
  border-radius: 6px;
  margin-bottom: 1.5rem;
  width: 100%;
  max-width: 100%;
}

.modal-buttons {
  display: flex;
  justify-content: space-between;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.modal-buttons .btn {
  flex: 1;
  padding: 0.6rem 1rem;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  border: none;
  transition: background 0.2s;
}

.btn-cancel {
  background-color: #e74c3c;
  color: #fff;
}

.btn-cancel:hover {
  background-color: #c0392b;
}

.btn-clear {
  background-color: #f39c12;
  color: #fff;
}

.btn-clear:hover {
  background-color: #d35400;
}

/* ===== DARK MODE ===== */
.v-theme--dark .content-wrapper {
  background: #2e344c;
  color: #eceff4;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.5);
}

.v-theme--dark .title,
.v-theme--dark h3 {
  color: #eceff4;
}

.v-theme--dark .field label,
.v-theme--dark .info-label {
  color: #d8dee9;
}

.v-theme--dark .field input,
.v-theme--dark .field textarea,
.v-theme--dark select,
.v-theme--dark .date-input {
  background-color: #3b4252;
  color: #eceff4;
  border: 1px solid #4c566a;
}

.v-theme--dark .epi-card {
  background: #3b4252;
  border: 1px solid #4c566a;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
}

.v-theme--dark .card-header {
  border-bottom: 1px solid #4c566a;
}

.v-theme--dark .epi-name {
  color: #eceff4;
}

.v-theme--dark .epi-ca {
  color: #b48ead;
}

.v-theme--dark .info-value {
  color: #d8dee9;
}

.v-theme--dark .signature-link {
  color: #a3be8c;
}

.v-theme--dark .not-signed {
  color: #bf616a;
}

.v-theme--dark .table-responsive table,
.v-theme--dark th,
.v-theme--dark td {
  border-color: #4c566a;
}

.v-theme--dark tbody tr:nth-child(even) {
  background-color: #2e344c;
}

.v-theme--dark tbody tr:hover {
  background-color: #434c6e;
}

.v-theme--dark .modal-content {
  background-color: #3b4252;
  color: #eceff4;
}

.v-theme--dark .signature-canvas {
  border: 1px solid #4c566a;
}
</style>

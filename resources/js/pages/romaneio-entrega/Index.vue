<script setup>
import { ref, nextTick, onMounted } from "vue";
import axios from "axios";
import Swal from "sweetalert2";
import SignaturePad from "signature_pad";
import { FingerprintSpinner } from "epic-spinners";

const loading = ref(false);
const loadingDetalhes = ref(false);
const filtros = ref({ funcionario: "", dataInicial: "", dataFinal: "" });
const romaneios = ref([]);
const modalAberto = ref(false);
const modalDetalhesAberto = ref(false);
const canvasRef = ref(null);
const signaturePad = ref(null);
const romaneioSelecionado = ref(null);
const detalhesBaixa = ref(null);

// paginação server-side
const currentPage = ref(1);
const totalPages = ref(1);
const totalItems = ref(0);
const perPage = ref(50);

const buscarRomaneios = async (page = 1) => {
  loading.value = true;
  romaneios.value = [];

  try {
    const params = {
      page: page,
    };
    if (filtros.value.funcionario)
      params.COD_FUNCIONARIO = filtros.value.funcionario;
    if (filtros.value.dataInicial && filtros.value.dataFinal) {
      params.DATA_INICIAL = filtros.value.dataInicial;
      params.DATA_FINAL = filtros.value.dataFinal;
    }
    const { data } = await axios.get(
      `${import.meta.env.VITE_URL_SITE}/api/romaneio/index`,
      { params }
    );

    // Atualiza os dados com a resposta paginada
    romaneios.value = data.data || [];
    currentPage.value = data.current_page || 1;
    totalPages.value = data.last_page || 1;
    totalItems.value = data.total || 0;
  } catch (error) {
    Swal.fire(
      "Erro",
      error.response?.data?.message || "Falha ao buscar romaneios",
      "error"
    );
    console.log(error);
  } finally {
    loading.value = false;
  }
};

const mudarPagina = (novaPagina) => {
  if (novaPagina >= 1 && novaPagina <= totalPages.value) {
    buscarRomaneios(novaPagina);
  }
};

const abrirAssinatura = (romaneio) => {
  romaneioSelecionado.value = romaneio;
  modalAberto.value = true;
  nextTick(() => {
    signaturePad.value = new SignaturePad(canvasRef.value);
  });
};

const fecharModal = () => {
  modalAberto.value = false;
  signaturePad.value = null;
};

const assinarRomaneio = async () => {
  if (!romaneioSelecionado.value) return;

  loading.value = true;
  const dataUrl = signaturePad.value.toDataURL("image/png");
  fecharModal();

  try {
    // Envia apenas o código do romaneio para criar uma única assinatura
    await axios.post(
      `${import.meta.env.VITE_URL_SITE}/api/assinatura/romaneio/create`,
      {
        codigo_romaneio: romaneioSelecionado.value.CODIGO,
        assinatura: dataUrl,
      }
    );

    Swal.fire("Sucesso", "Romaneio assinado com sucesso!", "success");
    await buscarRomaneios();
  } catch (error) {
    Swal.fire(
      "Erro",
      error.response?.data?.message ||
        error.message ||
        "Falha ao assinar romaneio",
      "error"
    );
  } finally {
    loading.value = false;
  }
};

const formatDate = (dateString) => {
  if (!dateString) return "-";
  try {
    // Se já estiver no formato DD/MM/YYYY, retorna como está
    if (dateString.includes("/")) {
      return dateString;
    }

    // Se estiver no formato YYYY-MM-DD, converte para DD/MM/YYYY
    if (typeof dateString === "string" && dateString.includes("-")) {
      const parts = dateString.split(" ")[0].split("-"); // Remove hora se houver
      if (parts.length === 3) {
        const [year, month, day] = parts;
        return `${day}/${month}/${year}`;
      }
    }

    // Fallback: tenta usar Date, mas ajusta para evitar problema de timezone
    const date = new Date(dateString + "T00:00:00"); // Força meia-noite no timezone local
    return date.toLocaleDateString("pt-BR");
  } catch {
    return dateString;
  }
};

const formatCurrency = (value) => {
  if (!value) return "R$ 0,00";
  return new Intl.NumberFormat("pt-BR", {
    style: "currency",
    currency: "BRL",
  }).format(value);
};

const abrirDetalhes = async (romaneio) => {
  loadingDetalhes.value = true;
  modalDetalhesAberto.value = true;
  detalhesBaixa.value = null;

  try {
    const { data } = await axios.get(
      `${import.meta.env.VITE_URL_SITE}/api/romaneio/details/${romaneio.CODIGO}`
    );
    detalhesBaixa.value = data;
  } catch (error) {
    console.error("Erro ao buscar detalhes:", error);
    const errorMessage =
      error.response?.data?.message ||
      error.response?.data?.error ||
      error.message ||
      "Falha ao carregar detalhes da baixa";
    Swal.fire("Erro", errorMessage, "error");
    modalDetalhesAberto.value = false;
  } finally {
    loadingDetalhes.value = false;
  }
};

const fecharModalDetalhes = () => {
  modalDetalhesAberto.value = false;
  detalhesBaixa.value = null;
};

onMounted(() => {
  buscarRomaneios();
});
</script>

<template>
  <div>
    <div class="page-header">
      <div class="page-titles">
        <div class="breadcrumb">
          <span class="page-title-text">Baixas de Produtos</span>
          <span class="pipe"> | </span>
          <span class="breadcrumb-icon" aria-hidden="true">
            <VIcon size="18" icon="tabler-box" />
          </span>
          <span class="sep"> › </span>
          <span class="crumb-current">Listagem</span>
        </div>
      </div>
    </div>

    <div class="content-wrapper">
      <div v-if="loading" class="spinner-area">
        <fingerprint-spinner
          :animation-duration="1200"
          :size="48"
          color="#2c3e50" />
      </div>

      <div v-else class="romaneio-list">
        <div v-if="!romaneios.length" class="empty-state">
          <VIcon size="64" icon="tabler-inbox-off" />
          <h3>Nenhum romaneio encontrado</h3>
          <p>Não há romaneios cadastrados no sistema no momento.</p>
        </div>

        <div v-else class="table-container-wrapper">
          <div class="table-header">
            <div class="table-title">
              <VIcon size="22" icon="tabler-list-check" />
              <span>Lista de Romaneios</span>
              <span class="badge">{{ totalItems }}</span>
            </div>
          </div>

          <div class="table-container">
            <table class="table">
              <thead>
                <tr>
                  <th>
                    <VIcon size="18" icon="tabler-hash" />
                    Nº BAIXA
                  </th>
                  <th>
                    <VIcon size="18" icon="tabler-building" />
                    Nº PROJETO
                  </th>
                  <th>
                    <VIcon size="18" icon="tabler-user" />
                    FUNCIONARIO R/
                  </th>
                  <th>
                    <VIcon size="18" icon="tabler-user-check" />
                    FUNCIONARIO ENTREGOU
                  </th>
                  <th>
                    <VIcon size="18" icon="tabler-calendar-event" />
                    Data Entrega
                  </th>
                  <th>
                    <VIcon size="18" icon="tabler-alert-circle" />
                    Status
                  </th>
                  <th>
                    <VIcon size="18" icon="tabler-settings" />
                    Ações
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="r in romaneios" :key="r.CODIGO">
                  <td class="code-cell">
                    <span class="code-badge">#{{ r.NUM_BAIXA }}</span>
                  </td>
                  <td class="text-center">
                    {{ r.NUM_PROJETO || "-" }}
                  </td>
                  <td class="date-cell">
                    <span class="number-cell">{{
                      r.NOME_FUNCIONARIO_RECEBE
                    }}</span>
                  </td>
                  <td class="date-cell">
                    <span class="number-cell">{{
                      r.NOME_FUNCIONARIO_ENTREGA || "-"
                    }}</span>
                  </td>
                  <td class="date-cell">{{ formatDate(r.DATA_ENTREGA) }}</td>
                  <td class="status-cell">
                    <span v-if="r.ASSINADO" class="status-badge status-success">
                      <VIcon size="16" icon="tabler-check" />
                      Assinado
                    </span>
                    <span v-else class="status-badge status-warning">
                      <VIcon size="16" icon="tabler-clock" />
                      Pendente
                    </span>
                  </td>
                  <td class="actions-cell">
                    <div
                      style="
                        display: flex;
                        gap: 0.5rem;
                        justify-content: center;
                      ">
                      <button
                        class="action-button action-button-view"
                        @click="abrirDetalhes(r)"
                        title="Visualizar detalhes">
                        <VIcon size="18" icon="tabler-eye" />
                      </button>
                      <button
                        class="action-button"
                        @click="abrirAssinatura(r)"
                        :disabled="r.ASSINADO">
                        <VIcon size="18" icon="tabler-pencil" />
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="table-footer">
            <div class="rows-per-page">
              <VIcon size="18" icon="tabler-list-numbers" />
              <label>Total de registros: {{ totalItems }}</label>
            </div>
            <div class="pagination">
              <button
                class="page-btn"
                :disabled="currentPage === 1"
                @click="mudarPagina(currentPage - 1)">
                <VIcon size="18" icon="tabler-chevron-left" />
                Anterior
              </button>
              <span class="page-info"
                >Página {{ currentPage }} de {{ totalPages }}</span
              >
              <button
                class="page-btn"
                :disabled="currentPage === totalPages"
                @click="mudarPagina(currentPage + 1)">
                Próxima
                <VIcon size="18" icon="tabler-chevron-right" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal de Detalhes -->
      <div v-if="modalDetalhesAberto" class="modal">
        <div class="modal-overlay" @click="fecharModalDetalhes"></div>
        <div class="modal-content modal-detalhes">
          <div class="modal-header">
            <VIcon size="24" icon="tabler-file-text" />
            <h3>
              Detalhes da Baixa #{{
                detalhesBaixa?.baixa?.NUM_BAIXA || detalhesBaixa?.baixa?.CODIGO
              }}
            </h3>
          </div>
          <div v-if="loadingDetalhes" class="spinner-area-modal">
            <fingerprint-spinner
              :animation-duration="1200"
              :size="48"
              color="#2c3e50" />
          </div>
          <div v-else-if="detalhesBaixa" class="detalhes-content">
            <div class="detalhes-grid">
              <div class="detalhes-field">
                <label>
                  <VIcon size="18" icon="tabler-calendar-event" />
                  Data de Entrega
                </label>
                <input
                  type="text"
                  :value="formatDate(detalhesBaixa.baixa.DATA_ENTREGA)"
                  disabled
                  class="input-disabled" />
              </div>
              <div class="detalhes-field">
                <label>
                  <VIcon size="18" icon="tabler-user" />
                  Funcionário que Recebe
                </label>
                <input
                  type="text"
                  :value="detalhesBaixa.baixa.FUNCIONARIO_RECEBE?.NOME || '-'"
                  disabled
                  class="input-disabled" />
              </div>
              <div class="detalhes-field">
                <label>
                  <VIcon size="18" icon="tabler-building" />
                  Nome do Projeto
                </label>
                <input
                  type="text"
                  :value="detalhesBaixa?.baixa?.NUM_PROJETO || '-'"
                  disabled
                  class="input-disabled" />
              </div>
              <div class="detalhes-field">
                <label>
                  <VIcon size="18" icon="tabler-folder" />
                  Centro de Custo
                </label>
                <input
                  type="text"
                  :value="
                    detalhesBaixa.baixa.CENTRO_CUSTO ||
                    detalhesBaixa.baixa.COD_CENTRO_CUSTO ||
                    '-'
                  "
                  disabled
                  class="input-disabled" />
              </div>
              <div class="detalhes-field">
                <label>
                  <VIcon size="18" icon="tabler-user-check" />
                  Funcionário que Entrega
                </label>
                <input
                  type="text"
                  :value="detalhesBaixa.baixa.FUNCIONARIO_ENTREGA?.NOME || '-'"
                  disabled
                  class="input-disabled" />
              </div>
              <div class="detalhes-field full-width">
                <label>
                  <VIcon size="18" icon="tabler-note" />
                  Observações
                </label>
                <textarea
                  :value="detalhesBaixa.baixa.OBS || '-'"
                  disabled
                  class="input-disabled textarea-disabled"
                  rows="3"></textarea>
              </div>
            </div>

            <div class="produtos-section">
              <h4>
                <VIcon size="20" icon="tabler-package" />
                Produtos Entregues
              </h4>
              <div class="table-container-detalhes">
                <table class="table-detalhes">
                  <thead>
                    <tr>
                      <th>Descrição</th>
                      <th>Quantidade</th>
                      <th>Preço Unit.</th>
                      <th>Subtotal</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr
                      v-for="produto in detalhesBaixa.produtos"
                      :key="produto.CODIGO">
                      <td>{{ produto.DESCRICAO || "-" }}</td>
                      <td class="text-center">{{ produto.QUANTIDADE || 0 }}</td>
                      <td class="text-right">
                        {{ formatCurrency(produto.PRECO) }}
                      </td>
                      <td class="text-right">
                        {{ formatCurrency(produto.SUBTOTAL) }}
                      </td>
                      <td class="text-center">
                        <span
                          v-if="produto.ASSINADO"
                          class="status-badge status-success-small">
                          <VIcon size="14" icon="tabler-check" />
                          Assinado
                        </span>
                        <span v-else class="status-badge status-warning-small">
                          <VIcon size="14" icon="tabler-clock" />
                          Pendente
                        </span>
                      </td>
                    </tr>
                    <tr
                      v-if="
                        !detalhesBaixa.produtos ||
                        detalhesBaixa.produtos.length === 0
                      ">
                      <td colspan="5" class="text-center">
                        Nenhum produto encontrado
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <div v-if="detalhesBaixa.assinatura_url" class="assinatura-section">
              <h4>
                <VIcon size="20" icon="tabler-signature" />
                Assinatura do Recebimento
              </h4>
              <div class="signature-box">
                <img
                  :src="detalhesBaixa.assinatura_url"
                  alt="Assinatura da baixa"
                  class="signature-image" />
              </div>
            </div>
          </div>
          <div class="modal-buttons">
            <button class="btn-secondary" @click="fecharModalDetalhes">
              <VIcon size="18" icon="tabler-x" />
              Fechar
            </button>
          </div>
        </div>
      </div>

      <!-- Modal de Assinatura -->
      <div v-if="modalAberto" class="modal">
        <div class="modal-overlay" @click="fecharModal"></div>
        <div class="modal-content">
          <div class="modal-header">
            <VIcon size="24" icon="tabler-signature" />
            <h3>Assinar Romaneio #{{ romaneioSelecionado?.CODIGO }}</h3>
          </div>
          <canvas
            ref="canvasRef"
            width="400"
            height="200"
            class="signature-canvas"></canvas>
          <div class="modal-buttons">
            <button class="btn-secondary" @click="fecharModal">
              <VIcon size="18" icon="tabler-x" />
              Cancelar
            </button>
            <button class="btn-warning" @click="signaturePad?.clear()">
              <VIcon size="18" icon="tabler-eraser" />
              Limpar
            </button>
            <button class="btn-primary" @click="assinarRomaneio">
              <VIcon size="18" icon="tabler-check" />
              Salvar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* ===== HEADER ===== */
.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 1.5rem;
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

/* ===== CONTENT WRAPPER ===== */
.content-wrapper {
  width: 100%;
  max-width: 1400px;
  background-color: #ffffff;
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}

/* ===== SPINNER ===== */
.spinner-area {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 300px;
}

/* ===== EMPTY STATE ===== */
.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  color: #999;
}

.empty-state h3 {
  margin: 1rem 0 0.5rem;
  font-size: 1.5rem;
  color: #555;
}

.empty-state p {
  color: #777;
  font-size: 1rem;
}

/* ===== TABLE CONTAINER ===== */
.table-container-wrapper {
  background: #fff;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.table-header {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #e0e4e7;
}

.table-title {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 1.1rem;
  font-weight: 700;
  color: #2c3e50;
}

.table-title .badge {
  background: #1e88e5;
  color: #fff;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.875rem;
  font-weight: 600;
}

.table-container {
  overflow-x: auto;
}

.table {
  width: 100%;
  border-collapse: collapse;
  background: #fff;
}

.table th {
  background: #f8f9fa;
  padding: 1rem;
  text-align: left;
  font-weight: 700;
  font-size: 0.875rem;
  color: #495057;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  white-space: nowrap;
  border-bottom: 2px solid #e0e4e7;
  vertical-align: middle;
}

.table th svg {
  display: inline-block;
  vertical-align: middle;
  margin-right: 0.5rem;
}

.table td {
  padding: 1.25rem 1rem;
  border-bottom: 1px solid #f0f0f0;
  vertical-align: middle;
}

.table tbody tr {
  transition: all 0.2s ease;
}

.table tbody tr:hover {
  background-color: #f8f9fa;
}

.table tbody tr:last-child td {
  border-bottom: none;
}

/* ===== TABLE CELLS ===== */
.code-cell {
  font-weight: 600;
}

.code-badge {
  display: inline-block;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: #fff;
  padding: 0.4rem 0.8rem;
  border-radius: 6px;
  font-size: 0.875rem;
  font-weight: 700;
}

.date-cell {
  color: #6c757d;
  font-size: 0.95rem;
}

.number-cell {
  text-align: center;
}

.badge-info {
  display: inline-block;
  background: #e3f2fd;
  color: #1976d2;
  padding: 0.35rem 0.75rem;
  border-radius: 6px;
  font-weight: 600;
  font-size: 0.875rem;
}

.status-cell {
  text-align: center;
}

.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 600;
  white-space: nowrap;
}

.status-success {
  background: #e8f5e9;
  color: #2e7d32;
}

.status-warning {
  background: #fff3e0;
  color: #e65100;
}

.actions-cell {
  text-align: center;
}

.action-button {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.6rem 1.25rem;
  border: none;
  border-radius: 8px;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  white-space: nowrap;
}

.action-button:not(:disabled) {
  background: linear-gradient(135deg, #1e88e5 0%, #1565c0 100%);
  color: #fff;
  box-shadow: 0 2px 8px rgba(30, 136, 229, 0.2);
}

.action-button:not(:disabled):hover {
  background: linear-gradient(135deg, #1565c0 0%, #0d47a1 100%);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(30, 136, 229, 0.3);
}

.action-button:disabled {
  background: #e9ecef;
  color: #6c757d;
  cursor: not-allowed;
  opacity: 0.7;
}

.action-button-view {
  background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
  box-shadow: 0 2px 8px rgba(23, 162, 184, 0.2) !important;
}

.action-button-view:hover {
  background: linear-gradient(135deg, #138496 0%, #117a8b 100%) !important;
  box-shadow: 0 4px 12px rgba(23, 162, 184, 0.3) !important;
}

/* ===== TABLE FOOTER ===== */
.table-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 1rem 1.5rem;
  background: #f8f9fa;
  border-top: 1px solid #e0e4e7;
}

.rows-per-page {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #495057;
  font-size: 0.875rem;
}

.rows-per-page label {
  font-weight: 600;
}

.rows-per-page select {
  padding: 0.4rem 0.75rem;
  border: 1.5px solid #dee2e6;
  border-radius: 6px;
  font-size: 0.875rem;
  background: #fff;
  cursor: pointer;
  transition: all 0.2s ease;
}

.rows-per-page select:focus {
  outline: none;
  border-color: #1e88e5;
  box-shadow: 0 0 0 3px rgba(30, 136, 229, 0.1);
}

.pagination {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.page-info {
  color: #495057;
  font-weight: 600;
  font-size: 0.875rem;
}

.page-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border: 1.5px solid #dee2e6;
  background: #fff;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.875rem;
  font-weight: 600;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.page-btn:not(:disabled):hover {
  border-color: #1e88e5;
  color: #1e88e5;
  background: #e3f2fd;
}

.page-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  background: #f8f9fa;
}

/* ===== MODAL ===== */
.modal {
  position: fixed;
  inset: 0;
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}

.modal-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(2px);
}

.modal-content {
  position: relative;
  background: #fff;
  padding: 2rem;
  border-radius: 12px;
  min-width: 500px;
  max-width: 90vw;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
  z-index: 1;
}

.modal-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid #e0e4e7;
}

.modal-header h3 {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 700;
  color: #2c3e50;
}

.signature-canvas {
  border: 2px solid #e0e4e7;
  border-radius: 8px;
  display: block;
  margin: 1.5rem 0;
  width: 100%;
  background: #fafafa;
  cursor: crosshair;
}

.modal-buttons {
  display: flex;
  gap: 0.75rem;
  justify-content: flex-end;
  margin-top: 1.5rem;
}

.modal-buttons button {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-primary {
  background: linear-gradient(135deg, #1e88e5 0%, #1565c0 100%);
  color: #fff;
  box-shadow: 0 2px 8px rgba(30, 136, 229, 0.2);
}

.btn-primary:hover {
  background: linear-gradient(135deg, #1565c0 0%, #0d47a1 100%);
  box-shadow: 0 4px 12px rgba(30, 136, 229, 0.3);
  transform: translateY(-1px);
}

.btn-secondary {
  background: #e9ecef;
  color: #495057;
  border: 1.5px solid #dee2e6;
}

.btn-secondary:hover {
  background: #dee2e6;
  border-color: #ced4da;
}

.btn-warning {
  background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
  color: #fff;
  box-shadow: 0 2px 8px rgba(255, 152, 0, 0.2);
}

.btn-warning:hover {
  background: linear-gradient(135deg, #f57c00 0%, #e65100 100%);
  box-shadow: 0 4px 12px rgba(255, 152, 0, 0.3);
  transform: translateY(-1px);
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
  .content-wrapper {
    padding: 1rem;
  }

  .table-header {
    padding: 0.75rem 1rem;
  }

  .table th,
  .table td {
    padding: 0.75rem 0.5rem;
    font-size: 0.875rem;
  }

  .table-title {
    font-size: 1rem;
  }

  .table-footer {
    flex-direction: column;
    align-items: stretch;
    gap: 1rem;
  }

  .pagination {
    justify-content: center;
  }

  .modal-content {
    min-width: auto;
    padding: 1.5rem;
  }

  .modal-buttons {
    flex-direction: column;
  }

  .modal-buttons button {
    width: 100%;
    justify-content: center;
  }
}

/* ===== DARK MODE ===== */
.v-theme--dark .breadcrumb {
  color: #d8dee9;
}

.v-theme--dark .content-wrapper {
  background-color: #2e344c;
  color: #eceff4;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
}

.v-theme--dark .breadcrumb .page-title-text {
  color: #eceff4;
}

.v-theme--dark .breadcrumb-icon {
  color: #88c0d0;
}

.v-theme--dark .empty-state {
  color: #d8dee9;
}

.v-theme--dark .empty-state h3 {
  color: #eceff4;
}

.v-theme--dark .empty-state p {
  color: #b48ead;
}

.v-theme--dark .table-container-wrapper {
  background: #252836;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.v-theme--dark .table-header {
  background: linear-gradient(135deg, #3b4252 0%, #434c6e 100%);
  border-bottom-color: #4c566a;
}

.v-theme--dark .table-title {
  color: #eceff4;
}

.v-theme--dark .table-title .badge {
  background: #5e81ac;
}

.v-theme--dark .table {
  background: #252836;
  color: #eceff4;
}

.v-theme--dark .table th {
  background: #2e344c;
  color: #d8dee9;
  border-bottom-color: #4c566a;
}

.v-theme--dark .table td {
  border-bottom-color: #4c566a;
  color: #eceff4;
}

.v-theme--dark .table tbody tr:hover {
  background-color: #3b4252;
}

.v-theme--dark .table tbody tr:last-child td {
  border-bottom-color: #4c566a;
}

.v-theme--dark .code-badge {
  background: linear-gradient(135deg, #5e81ac 0%, #88c0d0 100%);
  color: #2e3440;
}

.v-theme--dark .date-cell {
  color: #81a1c1;
}

.v-theme--dark .badge-info {
  background: #434c6e;
  color: #88c0d0;
}

.v-theme--dark .status-success {
  background: #2e7d32;
  color: #c8e6c9;
}

.v-theme--dark .status-warning {
  background: #e65100;
  color: #ffe0b2;
}

.v-theme--dark .action-button:disabled {
  background: #3b4252;
  color: #5e6e7e;
}

.v-theme--dark .table-footer {
  background: #2e344c;
  border-top-color: #4c566a;
}

.v-theme--dark .rows-per-page {
  color: #d8dee9;
}

.v-theme--dark .rows-per-page select {
  background: #3b4252;
  color: #eceff4;
  border-color: #4c566a;
}

.v-theme--dark .rows-per-page select:focus {
  border-color: #5e81ac;
  box-shadow: 0 0 0 3px rgba(94, 129, 172, 0.2);
}

.v-theme--dark .rows-per-page select option {
  background: #3b4252;
  color: #eceff4;
}

.v-theme--dark .page-info {
  color: #d8dee9;
}

.v-theme--dark .page-btn {
  background: #3b4252;
  border-color: #4c566a;
  color: #eceff4;
}

.v-theme--dark .page-btn:not(:disabled):hover {
  border-color: #5e81ac;
  color: #88c0d0;
  background: #434c6e;
}

.v-theme--dark .page-btn:disabled {
  background: #252836;
  opacity: 0.5;
}

.v-theme--dark .modal-overlay {
  background: rgba(0, 0, 0, 0.7);
}

.v-theme--dark .modal-content {
  background: #252836;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
}

.v-theme--dark .modal-header {
  border-bottom-color: #4c566a;
}

.v-theme--dark .modal-header h3 {
  color: #eceff4;
}

.v-theme--dark .signature-canvas {
  border-color: #4c566a;
  background: #2e344c;
}

.v-theme--dark .btn-secondary {
  background: #3b4252;
  color: #eceff4;
  border-color: #4c566a;
}

.v-theme--dark .btn-secondary:hover {
  background: #434c6e;
  border-color: #5e81ac;
}

.v-theme--dark .btn-primary {
  background: linear-gradient(135deg, #5e81ac 0%, #4c6a88 100%);
}

.v-theme--dark .btn-primary:hover {
  background: linear-gradient(135deg, #4c6a88 0%, #3d5570 100%);
}

.v-theme--dark .btn-warning {
  background: linear-gradient(135deg, #d08770 0%, #bf616a 100%);
}

.v-theme--dark .btn-warning:hover {
  background: linear-gradient(135deg, #bf616a 0%, #a85550 100%);
}

/* ===== MODAL DETALHES ===== */
.modal-detalhes {
  max-width: 900px;
  max-height: 90vh;
  overflow-y: auto;
}

.spinner-area-modal {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 3rem;
}

.detalhes-content {
  padding: 1rem 0;
}

.detalhes-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
  margin-bottom: 2rem;
}

.detalhes-field {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.detalhes-field.full-width {
  grid-column: 1 / -1;
}

.detalhes-field label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 600;
  font-size: 0.875rem;
  color: #495057;
}

.input-disabled,
.textarea-disabled {
  padding: 0.75rem;
  border: 1.5px solid #dee2e6;
  border-radius: 6px;
  background-color: #f8f9fa;
  color: #495057;
  font-size: 0.95rem;
  cursor: not-allowed;
  width: 100%;
}

.textarea-disabled {
  resize: none;
  font-family: inherit;
}

.produtos-section {
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 2px solid #e0e4e7;
}

.produtos-section h4 {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1rem;
  font-size: 1.1rem;
  font-weight: 700;
  color: #2c3e50;
}

.table-container-detalhes {
  overflow-x: auto;
  border-radius: 8px;
  border: 1px solid #e0e4e7;
}

.table-detalhes {
  width: 100%;
  border-collapse: collapse;
  background: #fff;
}

.table-detalhes th {
  background: #f8f9fa;
  padding: 0.75rem;
  text-align: left;
  font-weight: 700;
  font-size: 0.875rem;
  color: #495057;
  border-bottom: 2px solid #e0e4e7;
}

.table-detalhes td {
  padding: 0.75rem;
  border-bottom: 1px solid #f0f0f0;
  color: #495057;
}

.table-detalhes tbody tr:last-child td {
  border-bottom: none;
}

.table-detalhes tbody tr:hover {
  background-color: #f8f9fa;
}

.text-center {
  text-align: center;
}

.text-right {
  text-align: right;
}

.status-success-small,
.status-warning-small {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.25rem 0.75rem;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
}

.status-success-small {
  background: #e8f5e9;
  color: #2e7d32;
}

.status-warning-small {
  background: #fff3e0;
  color: #e65100;
}

.assinatura-section {
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 2px solid #e0e4e7;
}

.assinatura-section h4 {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1rem;
  font-size: 1.1rem;
  font-weight: 700;
  color: #2c3e50;
}

.signature-box {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 1rem;
  border: 1px solid #e0e4e7;
  border-radius: 8px;
  background-color: #f8f9fa;
}

.signature-image {
  max-width: 100%;
  max-height: 220px;
  object-fit: contain;
}

@media (max-width: 768px) {
  .detalhes-grid {
    grid-template-columns: 1fr;
  }

  .modal-detalhes {
    max-width: 95vw;
    padding: 1.5rem;
  }

  .table-container-detalhes {
    overflow-x: scroll;
  }

  .table-detalhes {
    min-width: 600px;
  }
}

.v-theme--dark .detalhes-field label {
  color: #d8dee9;
}

.v-theme--dark .input-disabled,
.v-theme--dark .textarea-disabled {
  background-color: #3b4252;
  border-color: #4c566a;
  color: #eceff4;
}

.v-theme--dark .produtos-section {
  border-top-color: #4c566a;
}

.v-theme--dark .produtos-section h4 {
  color: #eceff4;
}

.v-theme--dark .table-container-detalhes {
  border-color: #4c566a;
}

.v-theme--dark .table-detalhes {
  background: #252836;
}

.v-theme--dark .table-detalhes th {
  background: #2e344c;
  color: #d8dee9;
  border-bottom-color: #4c566a;
}

.v-theme--dark .table-detalhes td {
  color: #eceff4;
  border-bottom-color: #4c566a;
}

.v-theme--dark .table-detalhes tbody tr:hover {
  background-color: #3b4252;
}

.v-theme--dark .status-success-small {
  background: #2e7d32;
  color: #c8e6c9;
}

.v-theme--dark .status-warning-small {
  background: #e65100;
  color: #ffe0b2;
}

.v-theme--dark .assinatura-section {
  border-top-color: #4c566a;
}

.v-theme--dark .assinatura-section h4 {
  color: #eceff4;
}

.v-theme--dark .signature-box {
  border-color: #4c566a;
  background-color: #3b4252;
}
</style>

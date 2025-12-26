<script setup>
import { ref, onMounted, computed, nextTick } from "vue";
import { FingerprintSpinner } from "epic-spinners";
import { useRouter } from "vue-router";
import axios from "axios";
import Swal from "sweetalert2";
import jsPDF from "jspdf";
import autoTable from "jspdf-autotable";
import SignaturePad from "signature_pad";

const loading = ref(true);
const loadingButtonFilter = ref(false);
const loadingButtonFilterClear = ref(false);
const loadingButtonFilterInsert = ref(false);

const employees = ref([]);
const epis = ref([]);

const modalAberto = ref(false);
const signaturePad = ref(null);
const canvasRef = ref(null);

const employeesOptions = ref([]);
const selectedEmployee = ref("");
const functionOptions = ref([]);
const selectedFunction = ref("");
const setorOptions = ref([]);
const selectedSetor = ref("");
const selectedDate = ref("");
const selecionados = ref([]);

const router = useRouter();

const sortedEmployees = computed(() => {
  return [...employees.value].sort((a, b) => {
    const nameA = a.funcionario?.NOME || "";
    const nameB = b.funcionario?.NOME || "";
    return nameA.localeCompare(nameB);
  });
});

const filterFichaEpi = async () => {
  try {
    loadingButtonFilter.value = true;
    const params = {
      funcionario: selectedEmployee.value || "",
      funcao: selectedFunction.value || "",
      setor: selectedSetor.value || "",
      data: selectedDate.value || "",
    };
    const response = await axios.get(
      `${import.meta.env.VITE_URL_SITE}/api/ficha_epi/index`,
      { params }
    );
    employees.value = response.data;
  } catch (error) {
    Swal.fire(
      "Erro",
      error.response?.data?.message || "Falha ao filtrar fichas EPI",
      "error"
    );
  } finally {
    loadingButtonFilter.value = false;
  }
};

const fetchEmployees = async () => {
  try {
    const response = await axios.get(
      `${import.meta.env.VITE_URL_SITE}/api/funcionario/index`
    );
    employeesOptions.value = response.data;
  } catch (error) {
    Swal.fire(
      "Erro",
      error.response?.data?.message || "Falha ao carregar funcionários",
      "error"
    );
  }
};

const fetchFunctions = async () => {
  try {
    const response = await axios.get(
      `${import.meta.env.VITE_URL_SITE}/api/funcoes/index`
    );
    functionOptions.value = response.data;
  } catch (error) {
    Swal.fire(
      "Erro",
      error.response?.data?.message || "Falha ao carregar funcionários",
      "error"
    );
  }
};

const fetchSetor = async () => {
  try {
    const response = await axios.get(
      `${import.meta.env.VITE_URL_SITE}/api/setor/index`
    );
    setorOptions.value = response.data;
  } catch (error) {
    Swal.fire(
      "Erro",
      error.response?.data?.message || "Falha ao carregar funcionários",
      "error"
    );
  }
};

const fetchFichaEpi = async () => {
  try {
    const response = await axios.get(
      `${import.meta.env.VITE_URL_SITE}/api/ficha_epi/index`
    );
    employees.value = response.data;
    loading.value = false;
  } catch (error) {
    Swal.fire(
      "Erro",
      error.response?.data?.message || "Falha ao carregar funcionários",
      "error"
    );
  } finally {
    loadingButtonFilterClear.value = false;
  }
};

const details = (id) => {
  router.push({ name: "fichas-details", params: { id } });
};

const goToCreate = () => {
  router.push({ name: "fichas-epi-create" }).catch(() => {
    router.push("/fichas-epi/create");
  });
};

const clearFilter = () => {
  loadingButtonFilterClear.value = true;
  selectedEmployee.value = "";
  selectedFunction.value = "";
  selectedSetor.value = "";
  selectedDate.value = "";
  fetchFichaEpi();
};

const gerarPdf = async (id) => {
  try {
    loading.value = true;
    const response = await axios.get(
      `${import.meta.env.VITE_URL_SITE}/api/ficha_epi/show/${id}`
    );
    const data = response.data;
    const hoje = new Date().toLocaleDateString("pt-BR");
    data.dataEmissaoHoje = hoje;

    await gerarPdfAutoTable(data);
  } catch (error) {
    console.error("Erro ao gerar PDF:", error);
    Swal.fire("Erro", error.response.data.message, "error");
  } finally {
    loading.value = false;
  }
};

async function toBase64(url) {
  const res = await fetch(url);
  const blob = await res.blob();
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onloadend = () => resolve(reader.result);
    reader.onerror = reject;
    reader.readAsDataURL(blob);
  });
}

async function gerarPdfAutoTable(pdfData) {
  try {
    const doc = new jsPDF({
      orientation: "landscape",
      unit: "pt",
      format: "a4",
    });
    const pageWidth = doc.internal.pageSize.getWidth();
    const pageHeight = doc.internal.pageSize.getHeight();

    const logoBase64 = await toBase64(
      "https://mempartestes.infotech.app.br/build/assets/infotech250x250-CrEgOxiQ.png"
    );

    const body = await Promise.all(
      pdfData.epis.map(async (item) => {
        let assinaturaBase64 = "";
        if (item.ASSINADO) assinaturaBase64 = await toBase64(item.ASSINADO);

        return {
          dataEntrega: convertDate(item.DATA_ENTREGA) || "",
          dataDevolucao:
            convertDate(item.DATA_DEVOLUCAO || item.DATA_ENTREGA) || "",
          quantidade: item.QTD || "",
          descricao: item.NOME || "",
          numCA: item.NUM_CA || "",
          motivo: item.OBS || "",
          signatureData: assinaturaBase64,
        };
      })
    );

    const headerHeight = 260;
    const bodyData = body.map((row) => [
      row.dataEntrega,
      row.dataDevolucao,
      row.quantidade,
      row.descricao,
      row.numCA,
      row.motivo,
      "",
    ]);

    autoTable(doc, {
      startY: headerHeight,
      head: [
        [
          "Entrega",
          "Devolução",
          "Qtde.",
          "Equipamento",
          "CA nº",
          "Motivo",
          "Assinatura",
        ],
      ],
      body: bodyData,
      theme: "grid",
      headStyles: {
        fillColor: [255, 255, 255],
        textColor: 0,
        lineColor: 0,
        lineWidth: 0.5,
      },
      bodyStyles: {
        fillColor: [255, 255, 255],
        textColor: 0,
        lineColor: 0,
        lineWidth: 0.5,
      },
      styles: { fontSize: 9, halign: "center", valign: "middle" },
      columnStyles: {
        3: { halign: "left" },
        6: { halign: "center", cellWidth: 100 },
      },
      margin: { top: headerHeight, bottom: 100 },

      didDrawPage: (data) => {
        doc.setFontSize(8);
        doc.text(`Documento Gerado em: ${pdfData.dataEmissaoHoje}`, 40, 20);

        doc.addImage(logoBase64, "PNG", 50, 70, 100, 30);

        doc.setFontSize(16);
        doc.setFont("helvetica", "bold");
        doc.text("Mempar", pageWidth / 2, 50, { align: "center" });
        doc.setFontSize(13);
        doc.text("Ficha de EPI - Trabalhadores", pageWidth / 2, 70, {
          align: "center",
        });
        doc.setFontSize(12);
        doc.text("Mempar Paranaguá ()", pageWidth / 2, 90, { align: "center" });
        doc.setFont("helvetica", "normal");

        const tableX = 40;
        const tableY = 110;
        const rowHeight = 32;
        const colWidths = [
          (pageWidth - 80) * 0.4,
          (pageWidth - 80) * 0.3,
          (pageWidth - 80) * 0.15,
          (pageWidth - 80) * 0.15,
        ];

        let xPos = tableX;
        const headers = [
          "Nome do trabalhador",
          "Função",
          "Matrícula",
          "Data de admissão",
        ];
        const values = [
          pdfData.ficha.funcionario?.NOME || "",
          pdfData.ficha.funcionario?.funcao?.FUNCAO || "",
          pdfData.ficha?.funcionario?.N_CARTEIRA || "",
          convertDate(pdfData.ficha?.funcionario?.DATA_ADMISSAO) || "",
        ];

        for (let i = 0; i < headers.length; i++) {
          doc.rect(xPos, tableY, colWidths[i], rowHeight);
          doc.setFontSize(8);
          doc.setFont("helvetica", "bold");
          doc.text(headers[i], xPos + 8, tableY + 10);
          doc.setFontSize(10);
          doc.setFont("helvetica", "normal");
          doc.text(String(values[i]), xPos + 8, tableY + 25);
          xPos += colWidths[i];
        }

        const textWidth = pageWidth - 80;
        const textX = tableX;
        const textY = tableY + 42;

        const text = `
          DECLARO ter recebido o(s) Equipamento(s) de Proteção Individual - EPI's., abaixo especificado(s) nos termos do artigo 166 e 167 da CLT, com redação dada pela Lei Federal nº 
          6.514/77, objetivando a proteção da incolumidade física, bem como a neutralização de possíveis agentes insalubres conforme o art. 191, inciso II, da norma jurídica mencionada,
          e ainda, o treinamento para o uso correto do(s) mesmo(s). COMPROMETO-ME a utilizá-los sempre para os fins a que se destinam, estando ciente que o não uso incorrerá contra
          a minha pessoa em ato faltoso, sujeitando-me as penalidades legais. RESPONSABILIZO-ME por sua guarda, conservação, uso correto, e a devolução ao SESMT em qualquer
          estado que se encontre o equipamento, indenizando a empresa no caso de perda, extravio ou danos por uso incorreto (art. 462, parágrafo 1º, da CLT), e, a comunicação ao
          superior hierárquico ou Técnico em Segurança do Trabalho caso ocorra qualquer alteração que o torne impróprio para o uso, sendo possível a retirada ou troca de EPI sempre
          que necessário.
        `;

        doc.rect(textX, textY - 10, textWidth, 90);
        doc.setFontSize(9);
        doc.text(text, textX, textY, { maxWidth: textWidth, align: "left" });

        doc.setFontSize(8);
        doc.setTextColor(150);
        doc.text(
          "Gerado pelo sistema Infotech.com",
          pageWidth / 2,
          pageHeight - 30,
          { align: "center" }
        );
      },
      didDrawCell: (data) => {
        if (data.section === "body" && data.column.index === 6) {
          const imgData = body[data.row.index].signatureData;
          if (imgData && imgData.startsWith("data:image")) {
            const { x, y, width, height } = data.cell;
            doc.addImage(imgData, "PNG", x + 2, y + 2, width - 4, height - 4);
            data.cell.text = [];
          }
        }
      },
    });

    doc.save(`FICHA EPI - ${pdfData.ficha.funcionario.NOME}.pdf`);
  } catch (error) {
    console.error("Erro ao gerar PDF com jspdf-autotable:", error);
  }
}

const convertDate = (date) => {
  if (!date) return "";
  if (typeof date !== "string") return "";

  const parts = date.split("-");
  if (parts.length !== 3) return date; // retorna original se formato inesperado

  const [year, month, day] = parts;
  return `${day}/${month}/${year}`;
};

const toggleSelecionarTodos = () => {
  const naoAssinados = employees.value
    .filter((e) => e.status_assinatura !== 2 && e.status_assinatura !== 0)
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
  modalAberto.value = true;
  nextTick(() => {
    signaturePad.value = new SignaturePad(canvasRef.value);
  });
};

const fecharModal = () => {
  modalAberto.value = false;
};

const salvarAssinaturaSelecionados = async () => {
  if (!selecionados.value.length) return;
  loading.value = true;
  const dataUrl = signaturePad.value.toDataURL("image/png");
  fecharModal();

  try {
    await axios.post(
      `${import.meta.env.VITE_URL_SITE}/api/assinatura/group/create`,
      {
        ids_fichas: selecionados.value,
        assinatura: dataUrl,
      }
    );

    Swal.fire("Sucesso", "Assinatura aplicada com sucesso", "success");
    selecionados.value = [];
    fetchFichaEpi();
    fetchEmployees();
    fetchFunctions();
    fetchSetor();
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
  fetchFichaEpi();
  fetchEmployees();
  fetchFunctions();
  fetchSetor();
});
</script>

<template>
  <div v-if="loading" class="overlay-spinner">
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
          <span class="crumb-current">Listagem</span>
        </div>
      </div>
      <div class="page-actions"></div>
    </div>

    <div class="filters-card">
      <div class="filters-header">
        <div class="filters-title">
          <VIcon size="22" icon="tabler-filter" />
          <span>Filtros de Busca</span>
        </div>
      </div>

      <div class="filters-body">
        <div class="filters-grid">
          <div class="filter-item">
            <label for="employee">
              <VIcon size="18" icon="tabler-user" />
              <span>Funcionário</span>
            </label>
            <div class="select-wrapper">
              <select id="employee" v-model="selectedEmployee">
                <option value="" disabled>Selecione um funcionário</option>
                <option
                  v-for="func in employeesOptions"
                  :key="func.CODIGO"
                  :value="func.CODIGO">
                  {{ func.NOME }}
                </option>
              </select>
              <VIcon
                size="18"
                icon="tabler-chevron-down"
                class="select-arrow" />
            </div>
          </div>

          <div class="filter-item">
            <label for="function">
              <VIcon size="18" icon="tabler-briefcase" />
              <span>Função</span>
            </label>
            <div class="select-wrapper">
              <select id="function" v-model="selectedFunction">
                <option value="" disabled>Selecione uma função</option>
                <option
                  v-for="func in functionOptions"
                  :key="func.CODIGO"
                  :value="func.CODIGO">
                  {{ func.FUNCAO }}
                </option>
              </select>
              <VIcon
                size="18"
                icon="tabler-chevron-down"
                class="select-arrow" />
            </div>
          </div>

          <div class="filter-item">
            <label for="setor">
              <VIcon size="18" icon="tabler-building" />
              <span>Setor</span>
            </label>
            <div class="select-wrapper">
              <select id="setor" v-model="selectedSetor">
                <option value="" disabled>Selecione um setor</option>
                <option
                  v-for="func in setorOptions"
                  :key="func.CODIGO"
                  :value="func.CODIGO">
                  {{ func.DEPARTAMENTO }}
                </option>
              </select>
              <VIcon
                size="18"
                icon="tabler-chevron-down"
                class="select-arrow" />
            </div>
          </div>

          <div class="filter-item">
            <label for="date">
              <VIcon size="18" icon="tabler-calendar" />
              <span>Data</span>
            </label>
            <input
              id="date"
              type="date"
              v-model="selectedDate"
              class="date-input" />
          </div>
        </div>

        <div class="filters-actions">
          <button
            class="filter-button filter-btn-primary"
            @click="filterFichaEpi"
            :disabled="loadingButtonFilter">
            <fingerprint-spinner
              v-if="loadingButtonFilter"
              :animation-duration="800"
              :size="20"
              color="#fff" />
            <template v-else>
              <VIcon size="18" icon="tabler-search" />
              <span>Filtrar</span>
            </template>
          </button>

          <button
            class="filter-button filter-btn-secondary"
            @click="clearFilter"
            :disabled="loadingButtonFilterClear">
            <fingerprint-spinner
              v-if="loadingButtonFilterClear"
              :animation-duration="800"
              :size="20"
              color="#fff" />
            <template v-else>
              <VIcon size="18" icon="tabler-x" />
              <span>Limpar</span>
            </template>
          </button>

          <button
            class="filter-button filter-btn-success"
            @click="abrirAssinaturaSelecionados"
            :disabled="selecionados.length === 0">
            <fingerprint-spinner
              v-if="loadingButtonFilterInsert"
              :animation-duration="800"
              :size="20"
              color="#fff" />
            <template v-else>
              <VIcon size="18" icon="tabler-pencil" />
              <span>Assinar Selecionados</span>
            </template>
          </button>

          <button class="create-button" @click="goToCreate">
            <VIcon size="20" icon="tabler-plus" />
            <span>Nova Ficha</span>
          </button>
        </div>
      </div>
    </div>

    <div class="mobile-select-all">
      <label>
        <input
          type="checkbox"
          :checked="
            selecionados.length &&
            selecionados.length ===
              employees.filter(
                (e) => e.status_assinatura !== 2 && e.status_assinatura !== 0
              ).length
          "
          @change="toggleSelecionarTodos" />
        Selecionar Todos
      </label>
    </div>

    <div class="table-container">
      <table class="desktop-table">
        <thead>
          <tr>
            <th>
              <input
                type="checkbox"
                :checked="selecionados.length !== epis.length"
                @change="toggleSelecionarTodos" />
            </th>
            <th>SITUAÇÃO</th>
            <th>DETALHES</th>
            <th>PDF</th>
            <th>DATA ADMISSÃO <span>&#x2195;</span></th>
            <th>DATA EMISSÃO <span>&#x2195;</span></th>
            <th>Funcionário <span>&#x2195;</span></th>
            <th>MATRÍCULA <span>&#x2195;</span></th>
            <th>FUNÇÃO <span>&#x2195;</span></th>
            <th>SETOR <span>&#x2195;</span></th>
            <th>DT. INICIAL ENT. <span>&#x2195;</span></th>
            <th>DT. FINAL ENT. <span>&#x2195;</span></th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="employees.length === 0">
            <td
              :colspan="10"
              style="text-align: center; padding: 1rem; color: #777">
              Nenhum registro encontrado.
            </td>
          </tr>
          <tr v-else v-for="employee in sortedEmployees" :key="employee.id">
            <td>
              <input
                type="checkbox"
                :value="employee.CODIGO"
                v-model="selecionados"
                :disabled="
                  employee.status_assinatura === 2 ||
                  employee.status_assinatura === 0
                " />
            </td>
            <td class="text-center">
              <span v-if="employee.status_assinatura === 2">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="20"
                  height="20"
                  fill="#27ae60"
                  viewBox="0 0 24 24">
                  <path
                    d="M20.285 6.708l-11.285 11.292-5.285-5.292 1.414-1.414 3.871 3.879 9.871-9.879z" />
                </svg>
              </span>

              <span v-else-if="employee.status_assinatura === 1">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="20"
                  height="20"
                  fill="#8d95b5"
                  viewBox="0 0 24 24">
                  <path
                    d="M12 0c-6.627 0-12 5.373-12 12 0 6.628 5.373 12 12 12 6.628 0 12-5.372 12-12 0-6.627-5.372-12-12-12zm1 17h-2v-2h2v2zm0-4h-2v-7h2v7z" />
                </svg>
              </span>

              <span v-else-if="employee.status_assinatura === -1">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="20"
                  height="20"
                  fill="#e74c3c"
                  viewBox="0 0 24 24">
                  <path
                    d="M12 10.586l4.95-4.95 1.414 1.414-4.95 4.95 4.95 4.95-1.414 1.414-4.95-4.95-4.95 4.95-1.414-1.414 4.95-4.95-4.95-4.95 1.414-1.414 4.95 4.95z" />
                </svg>
              </span>
            </td>
            <td class="icon-cell">
              <svg
                @click="details(employee.CODIGO)"
                style="width: 24px; height: 24px; cursor: pointer"
                viewBox="0 0 24 24"
                fill="#1E88E5">
                <path
                  d="M15.5 14h-.79l-.28-.27
                    A6.471 6.471 0 0 0 16 9.5
                    6.5 6.5 0 1 0 9.5 16
                    c1.61 0 3.09-.59 4.23-1.57l.27.28v.79
                    l5 4.99L20.49 19l-4.99-5zm-6 0
                    C8.01 14 6 11.99 6 9.5S8.01 5
                    10.5 5 15 7.01 15 9.5
                    12.99 14 10.5 14z" />
              </svg>
            </td>
            <td>
              <button
                @click="gerarPdf(employee.CODIGO)"
                style="border: none; background: none; cursor: pointer">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="24"
                  height="24"
                  viewBox="0 0 24 24"
                  :fill="'red'">
                  <path
                    d="M6 2c-1.1 0-2 .9-2 2v16c0 
                    1.1.9 2 2 2h12c1.1 0 2-.9 
                    2-2V8l-6-6H6zm7 7V3.5L18.5 
                    9H13zm-1 9h-2v-6h2v6zm4 0h-2v-4h2v4zm-8 
                    0H6v-2h2v2z" />
                </svg>
              </button>
            </td>
            <td>{{ convertDate(employee.funcionario?.DATA_ADMISSAO) }}</td>
            <td>{{ convertDate(employee.DATA_EMISSAO) }}</td>
            <td>{{ employee.funcionario?.NOME || "Não há registro" }}</td>
            <td>{{ employee.funcionario?.N_CARTEIRA }}</td>
            <td>
              {{ employee.funcionario?.funcao?.FUNCAO || "Não há registro" }}
            </td>
            <td>
              {{
                employee.funcionario?.departamento?.DEPARTAMENTO ||
                "Não há registro"
              }}
            </td>
            <td>{{ convertDate(employee.DATA_INICIAL_ENTREGA) }}</td>
            <td>{{ convertDate(employee.DATA_FINAL_ENTREGA) }}</td>
          </tr>
        </tbody>
      </table>

      <div class="mobile-cards">
        <div v-if="employees.length === 0" class="empty-state">
          Nenhum registro encontrado.
        </div>
        <div
          v-else
          v-for="employee in sortedEmployees"
          :key="employee.id"
          class="pro-card">
          <div class="card-header">
            <div class="card-select">
              <input
                v-if="
                  employee.status_assinatura !== 2 &&
                  employee.status_assinatura !== 0
                "
                type="checkbox"
                :value="employee.CODIGO"
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
              <h3 class="employee-name">
                {{ employee.funcionario?.NOME || "Não registrado" }}
              </h3>
              <span class="employee-id"
                >Matrícula:
                {{ employee.funcionario?.N_CARTEIRA || "N/A" }}</span
              >
            </div>
          </div>

          <div class="card-body">
            <div class="info-grid">
              <div class="info-item">
                <span class="info-label">Função:</span>
                <span class="info-value">{{
                  employee.funcionario?.funcao?.FUNCAO || "N/A"
                }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Setor:</span>
                <span class="info-value">{{
                  employee.funcionario?.departamento?.DEPARTAMENTO || "N/A"
                }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Admissão:</span>
                <span class="info-value">{{
                  convertDate(employee.funcionario?.DATA_ADMISSAO)
                }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Emissão:</span>
                <span class="info-value">{{
                  convertDate(employee.DATA_EMISSAO)
                }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Início Entrega:</span>
                <span class="info-value">{{
                  convertDate(employee.DATA_INICIAL_ENTREGA)
                }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Fim Entrega:</span>
                <span class="info-value">{{
                  convertDate(employee.DATA_FINAL_ENTREGA)
                }}</span>
              </div>
            </div>
          </div>

          <div class="card-footer">
            <button
              class="action-btn details-btn"
              @click="details(employee.CODIGO)"
              title="Ver detalhes">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="#1E88E5">
                <path
                  d="M15.5 14h-.79l-.28-.27
                A6.471 6.471 0 0 0 16 9.5
                6.5 6.5 0 1 0 9.5 16
                c1.61 0 3.09-.59 4.23-1.57l.27.28v.79
                l5 4.99L20.49 19l-4.99-5zm-6 0
                C8.01 14 6 11.99 6 9.5S8.01 5
                10.5 5 15 7.01 15 9.5
                12.99 14 10.5 14z" />
              </svg>
              Detalhes
            </button>

            <button
              class="action-btn pdf-btn"
              @click="gerarPdf(employee.CODIGO)"
              :disabled="employee.status_assinatura !== 2"
              :title="
                employee.status_assinatura === 2
                  ? 'Gerar PDF'
                  : 'PDF não disponível'
              ">
              <svg
                width="20"
                height="20"
                viewBox="0 0 24 24"
                :fill="
                  employee.status_assinatura === 2 ? '#e74c3c' : '#95a5a6'
                ">
                <path
                  d="M6 2c-1.1 0-2 .9-2 2v16c0 
                1.1.9 2 2 2h12c1.1 0 2-.9 
                2-2V8l-6-6H6zm7 7V3.5L18.5 
                9H13zm-1 9h-2v-6h2v6zm4 0h-2v-4h2v4zm-8 
                0H6v-2h2v2z" />
              </svg>
              PDF
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

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
</template>

<style scoped>
.mobile-select-all {
  display: none;
  margin-bottom: 1rem;
  font-weight: 600;
}

@media (max-width: 900px) {
  .mobile-select-all {
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
  .mobile-select-all input {
    width: 18px;
    height: 18px;
  }

  .card-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding-bottom: 0.75rem;
  }

  .card-select {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    flex-shrink: 0;
  }

  .card-select input[type="checkbox"] {
    width: 20px;
    height: 20px;
    margin: 0;
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
}

/* ===== MODAL ===== */
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

.btn-save {
  background-color: #2ecc71;
  color: #fff;
}
.btn-save:hover {
  background-color: #27ae60;
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

/* ===== SPINNER ===== */
.overlay-spinner {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(255, 255, 255, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

/* ===== CONTAINER ===== */
.content-wrapper {
  width: 100%;
  max-width: 1400px;
  background-color: #ffffff;
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

/* ===== PAGE HEADER / BREADCRUMB ===== */
.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 1.25rem;
}

.page-titles {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.page-title {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 700;
  color: #2c3e50;
}

.breadcrumb {
  font-size: 0.875rem;
  color: #7f8c8d;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.breadcrumb .sep {
  margin: 0 0.25rem;
}

.breadcrumb .page-title-text {
  font-weight: 700;
  color: #2c3e50;
}

.breadcrumb .pipe {
  margin: 0 0.5rem;
  color: #b0b8bf;
}

.breadcrumb-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  line-height: 1;
  color: #90a4ae;
}

.page-actions {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.create-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  background: linear-gradient(135deg, #1e88e5 0%, #1565c0 100%);
  color: #fff;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.95rem;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(30, 136, 229, 0.2);
}

.create-button:hover {
  background: linear-gradient(135deg, #1565c0 0%, #0d47a1 100%);
  box-shadow: 0 4px 12px rgba(30, 136, 229, 0.3);
  transform: translateY(-1px);
}

.create-button:active {
  transform: translateY(0);
}

/* ===== FILTERS ===== */
.filters-card {
  background: #fff;
  border: 1px solid #e0e4e7;
  border-radius: 12px;
  margin-bottom: 1.5rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  overflow: hidden;
}

.filters-header {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #e0e4e7;
}

.filters-title {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 1rem;
  font-weight: 700;
  color: #2c3e50;
}

.filters-title span {
  color: #495057;
}

.filters-body {
  padding: 1.5rem;
}

.filters-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(220px, 1fr));
  gap: 1.25rem;
  margin-bottom: 1.5rem;
}

.filters-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  align-items: center;
  justify-content: flex-end;
  padding-top: 1rem;
  border-top: 1px solid #f0f0f0;
}

.filter-item {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.filter-item label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  font-weight: 600;
  color: #495057;
  margin-bottom: 0;
}

.select-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.select-wrapper select {
  width: 100%;
  padding: 0.75rem 2.5rem 0.75rem 0.875rem;
  border: 1.5px solid #dee2e6;
  border-radius: 8px;
  font-size: 0.95rem;
  background-color: #fff;
  cursor: pointer;
  transition: all 0.2s ease;
  appearance: none;
  box-sizing: border-box;
}

.select-wrapper select:focus {
  outline: none;
  border-color: #1e88e5;
  box-shadow: 0 0 0 3px rgba(30, 136, 229, 0.1);
}

.select-wrapper select:hover {
  border-color: #adb5bd;
}

.select-arrow {
  position: absolute;
  right: 0.875rem;
  pointer-events: none;
  color: #6c757d;
}

.date-input {
  width: 100%;
  padding: 0.75rem 0.875rem;
  border: 1.5px solid #dee2e6;
  border-radius: 8px;
  font-size: 0.95rem;
  background-color: #fff;
  cursor: pointer;
  transition: all 0.2s ease;
  box-sizing: border-box;
}

.date-input:focus {
  outline: none;
  border-color: #1e88e5;
  box-shadow: 0 0 0 3px rgba(30, 136, 229, 0.1);
}

.date-input:hover {
  border-color: #adb5bd;
}

.filter-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  border: none;
  padding: 0.75rem 1.25rem;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.9rem;
  font-weight: 600;
  transition: all 0.3s ease;
  white-space: nowrap;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.filter-button:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.filter-button:active:not(:disabled) {
  transform: translateY(0);
}

.filter-button:disabled {
  cursor: not-allowed;
  opacity: 0.6;
}

.filter-btn-primary {
  background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
  color: #fff;
}

.filter-btn-primary:hover:not(:disabled) {
  background: linear-gradient(135deg, #1565c0 0%, #0d47a1 100%);
}

.filter-btn-secondary {
  background: linear-gradient(135deg, #757575 0%, #616161 100%);
  color: #fff;
}

.filter-btn-secondary:hover:not(:disabled) {
  background: linear-gradient(135deg, #616161 0%, #424242 100%);
}

.filter-btn-success {
  background: linear-gradient(135deg, #388e3c 0%, #2e7d32 100%);
  color: #fff;
}

.filter-btn-success:hover:not(:disabled) {
  background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%);
}

.filter-btn-success:disabled {
  background: #bdbdbd;
  color: #fff;
}

@media (max-width: 1024px) {
  .filters-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 600px) {
  .filters-header {
    padding: 0.875rem 1rem;
  }

  .filters-body {
    padding: 1rem;
  }

  .filters-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
    margin-bottom: 1rem;
  }

  .filters-actions {
    flex-direction: column;
    align-items: stretch;
  }

  .filter-button,
  .create-button {
    width: 100%;
    justify-content: center;
  }
}

/* ===== TABLE ===== */
.table-container {
  overflow-x: auto;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

table {
  width: 100%;
  border-collapse: collapse;
  text-align: center;
  background: #fff;
}

th,
td {
  padding: 1.25rem 1rem;
  border-bottom: 1px solid #f0f0f0;
  white-space: normal;
  word-wrap: break-word;
  max-width: 200px;
  vertical-align: middle;
}

th {
  background: #f8f9fa;
  font-weight: 700;
  font-size: 0.875rem;
  color: #495057;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 1rem;
}

th span {
  color: #aaa;
  margin-left: 0.25rem;
}

tbody tr {
  transition: all 0.2s ease;
}

tbody tr:nth-child(even) {
  background-color: #f8f9fa;
}

tbody tr:hover {
  background-color: #f0f7ff;
}

tbody tr:last-child td {
  border-bottom: none;
}

.icon-cell {
  text-align: center;
  font-size: 1.2rem;
  cursor: pointer;
  vertical-align: middle;
}
.check {
  color: #27ae60;
  font-weight: bold;
}
.cross {
  color: #c0392b;
  font-weight: bold;
}

/* ===== MOBILE CARDS OTIMIZADOS ===== */
.mobile-cards {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1rem;
  padding-top: 1rem;
}

.pro-card {
  background: #ffffff;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  padding: 1rem;
  display: flex;
  flex-direction: column;
  min-height: auto;
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

.card-header-info {
  flex: 1;
  min-width: 0;
}

.employee-name {
  font-size: 1rem;
  font-weight: 600;
  color: #2c3e50;
  margin: 0 0 0.25rem 0;
  line-height: 1.2;
  word-wrap: break-word;
  overflow-wrap: break-word;
}

.employee-id {
  font-size: 0.85rem;
  color: #7f8c8d;
  font-weight: 500;
}

/* Grid para informações - layout mais compacto */
.info-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.info-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.25rem 0;
  font-size: 0.85rem;
  line-height: 1.3;
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

/* Footer com botões mais compactos */
.card-footer {
  display: flex;
  gap: 0.5rem;
  padding-top: 0.75rem;
  border-top: 1px solid #f0f0f0;
  margin-top: auto;
}

.action-btn {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.5rem 0.75rem;
  border: none;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  flex: 1;
  justify-content: center;
  min-height: 36px;
}

.details-btn {
  background-color: #e3f2fd;
  color: #1976d2;
}

.details-btn:hover {
  background-color: #bbdefb;
}

.pdf-btn {
  background-color: #ffebee;
  color: #c62828;
}

.pdf-btn:disabled {
  background-color: #f5f5f5;
  color: #9e9e9e;
  cursor: not-allowed;
}

.pdf-btn:not(:disabled):hover {
  background-color: #ffcdd2;
}

/* Status icon ajustado */
.status-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 20px;
  height: 20px;
}

/* Ajustes para telas muito pequenas */
@media (max-width: 380px) {
  .pro-card {
    padding: 0.75rem;
  }

  .info-item {
    font-size: 0.8rem;
  }

  .action-btn {
    padding: 0.4rem 0.6rem;
    font-size: 0.75rem;
  }

  .employee-name {
    font-size: 0.9rem;
  }
}

.empty-state {
  text-align: center;
  padding: 2rem;
  color: #999;
  font-size: 1rem;
  background: #ffffff;
  border: 1px solid #e0e0e0;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

/* RESPONSIVO */
@media (max-width: 900px) {
  .desktop-table {
    display: none;
  }
  .mobile-cards {
    display: grid;
  }

  .content-wrapper {
    padding: 1rem;
  }
}
@media (min-width: 900px) {
  .desktop-table {
    display: table;
  }
  .mobile-cards {
    display: none;
  }
}

/* ===== DARK MODE ===== */
.v-theme--dark .content-wrapper {
  background-color: #2e344c;
  color: #eceff4;
}

.v-theme--dark .table-container {
  background: #252836;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.v-theme--dark table {
  background: #252836;
  color: #eceff4;
}

.v-theme--dark th {
  background: #2e344c;
  color: #d8dee9;
  border-bottom-color: #4c566a;
}

.v-theme--dark td {
  border-bottom-color: #4c566a;
  color: #eceff4;
}

.v-theme--dark tbody tr:last-child td {
  border-bottom-color: #4c566a;
}

.v-theme--dark .filters-card {
  background-color: #2e344c;
  border: 1px solid #4c566a;
}

.v-theme--dark .filters-header {
  background: linear-gradient(135deg, #3b4252 0%, #434c6e 100%);
  border-bottom-color: #4c566a;
}

.v-theme--dark .filters-title {
  color: #eceff4;
}

.v-theme--dark .filters-title span {
  color: #d8dee9;
}

.v-theme--dark .filters-actions {
  border-top-color: #4c566a;
}

.v-theme--dark .pro-card {
  background-color: #252836;
  color: #eceff4;
  border: 1px solid #4c566a;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.v-theme--dark tbody tr:nth-child(even) {
  background-color: #2e344c;
}

.v-theme--dark tbody tr:nth-child(odd) {
  background-color: #252836;
}

.v-theme--dark tbody tr:hover {
  background-color: #434c6e;
}

.v-theme--dark .filter-item label {
  color: #eceff4;
}

.v-theme--dark .filter-item input,
.v-theme--dark .filter-item select {
  background-color: #3b4252;
  color: #eceff4;
  border: 1.5px solid #4c566a;
}

.v-theme--dark .select-wrapper select:focus,
.v-theme--dark .date-input:focus {
  border-color: #5e81ac;
  box-shadow: 0 0 0 3px rgba(94, 129, 172, 0.2);
}

.v-theme--dark .select-wrapper select:hover,
.v-theme--dark .date-input:hover {
  border-color: #5e81ac;
}

.v-theme--dark .select-arrow {
  color: #81a1c1;
}

.v-theme--dark .date-input {
  background-color: #3b4252;
  color: #eceff4;
  border: 1.5px solid #4c566a;
}

.v-theme--dark .filters-actions .create-button {
  background: linear-gradient(135deg, #5e81ac 0%, #4c6a88 100%);
  color: #fff;
}

.v-theme--dark .filters-actions .create-button:hover {
  background: linear-gradient(135deg, #4c6a88 0%, #3d5570 100%);
}

.v-theme--dark .filter-item select option {
  background-color: #3b4252;
  color: #eceff4;
}

.v-theme--dark th,
.v-theme--dark td {
  border-color: #4c566a;
  color: #eceff4;
}

.v-theme--dark .filter-btn-primary {
  background: linear-gradient(135deg, #5e81ac 0%, #4c6a88 100%);
  color: #fff;
}

.v-theme--dark .filter-btn-primary:hover:not(:disabled) {
  background: linear-gradient(135deg, #4c6a88 0%, #3d5570 100%);
}

.v-theme--dark .filter-btn-secondary {
  background: linear-gradient(135deg, #5e6e7e 0%, #4c566a 100%);
  color: #fff;
}

.v-theme--dark .filter-btn-secondary:hover:not(:disabled) {
  background: linear-gradient(135deg, #4c566a 0%, #3b4252 100%);
}

.v-theme--dark .filter-btn-success {
  background: linear-gradient(135deg, #4caf50 0%, #388e3c 100%);
  color: #fff;
}

.v-theme--dark .filter-btn-success:hover:not(:disabled) {
  background: linear-gradient(135deg, #388e3c 0%, #2e7d32 100%);
}

.v-theme--dark .filter-btn-success:disabled {
  background: #5e6e7e;
  color: #fff;
}

.v-theme--dark .employee-name {
  color: #eceff4;
}

.v-theme--dark .employee-id {
  color: #b48ead;
}

.v-theme--dark .info-label {
  color: #e5e9f0;
}

.v-theme--dark .info-value {
  color: #d8dee9;
}

.v-theme--dark .card-header,
.v-theme--dark .card-footer {
  border-color: #4c566a;
}

.v-theme--dark .details-btn {
  background-color: #1e3a5f;
  color: #90caf9;
}

.v-theme--dark .details-btn:hover {
  background-color: #2d4a75;
}

.v-theme--dark .pdf-btn {
  background-color: #3f2b2d;
  color: #ef9a9a;
}

.v-theme--dark .pdf-btn:disabled {
  background-color: #374151;
  color: #6b7280;
}

.v-theme--dark .pdf-btn:not(:disabled):hover {
  background-color: #4a3537;
}

.v-theme--dark .empty-state {
  background: #252836;
  border: 1px solid #4c566a;
  color: #d8dee9;
}
</style>

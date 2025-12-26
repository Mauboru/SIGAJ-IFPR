<script setup>
import { ref, onMounted } from 'vue'
import Swal from 'sweetalert2'
import axios from 'axios'
import { useRouter } from 'vue-router'

const router = useRouter()

const epis = ref(0)
const fichas = ref(0)

const cards = ref([
  { title: 'Epis & Fichas', color: '#16a34a' }
])

const sections = ref([
  {
    title: 'Controlador',
    items: [
      { name: 'Fichas', route: '/fichas-epi/Create' },
    ]
  }
])

const fetchEpi = async () => {
  try {
    const response = await axios.get(`${import.meta.env.VITE_URL_SITE}/api/epi/getAllEpis`)
    epis.value = response.data.count ?? response.data // se seu backend já retorna count
  } catch (error) {
    Swal.fire('Erro', error.response?.data?.message || 'Erro ao buscar Epis', 'error')
  }
}

const fetchFichaEpi = async () => {
  try {
    const response = await axios.get(`${import.meta.env.VITE_URL_SITE}/api/ficha_epi/getAllFichas`)
    fichas.value = response.data.count ?? response.data
  } catch (error) {
    Swal.fire('Erro', error.response?.data?.message || 'Erro ao buscar Fichas', 'error')
  }
}

const goToCreate = (route) => {
  router.push(route)
}

onMounted(() => {
  fetchEpi()
  fetchFichaEpi()
})
</script>

<template>
  <div class="dashboard">
    <!-- Cards -->
    <div class="cards">
      <div
        v-for="(card, i) in cards"
        :key="i"
        class="card"
        :style="{ backgroundColor: card.color }"
      >
        <p class="card-title">{{ card.title }}</p>
        <div class="card-split">
          <div class="sub-card">
            <span>Epis</span>
            <strong>{{ epis }}</strong>
          </div>
          <div class="sub-card">
            <span>Fichas</span>
            <strong>{{ fichas }}</strong>
          </div>
        </div>
      </div>
    </div>

    <!-- Sections -->
    <div class="sections">
      <div v-for="(section, i) in sections" :key="i" class="section">
        <h2>{{ section.title }}</h2>
        <ul>
          <li v-for="(item, j) in section.items" :key="j">
            <span>{{ item.name }}</span>
            <button class="create-btn" @click="goToCreate(item.route)">Criar Ficha</button>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<style scoped>
.dashboard {
  padding: 24px;
}

.cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 16px;
  margin-bottom: 40px;
}

.card {
  border-radius: 16px;
  padding: 20px;
  color: white;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.card-title {
  font-size: 14px;
  opacity: 0.9;
  margin-bottom: 12px;
}

.card-split {
  display: flex;
  justify-content: space-between;
  gap: 16px;
}

.sub-card {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.sub-card span {
  font-size: 12px;
  opacity: 0.8;
}

.sub-card strong {
  font-size: 22px;
  font-weight: bold;
}

.sections {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 20px;
}

.section {
  background: white;
  border-radius: 16px;
  padding: 20px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.section h2 {
  font-size: 18px;
  font-weight: 600;
  color: #374151;
  margin-bottom: 12px;
  padding-bottom: 8px;
  border-bottom: 1px solid #e5e7eb;
}

.section ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.section li {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 14px;
  padding: 6px 0;
  border-bottom: 1px solid #f3f4f6;
  color: #4b5563;
}

.section li:last-child {
  border-bottom: none;
}

.create-btn {
  background: #16a34a;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 12px;
  transition: background 0.2s;
}

.create-btn:hover {
  background: #15803d;
}
</style>

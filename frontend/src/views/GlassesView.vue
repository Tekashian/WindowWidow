<template>
  <div class="glasses-view">
    <div class="header">
      <h1>Typy Szkła</h1>
      <button @click="showForm = true" class="btn btn-primary">+ Dodaj szkło</button>
    </div>

    <SearchFilterBar 
      v-model="searchQuery"
      @update:modelValue="handleSearch"
      placeholder="Szukaj szkła po nazwie, typie..."
    />

    <LoadingSpinner v-if="loading" size="large" message="Ładowanie szkieł..." />

    <table v-if="!loading && glasses.length > 0" class="table">
      <thead>
        <tr>
          <th>Nazwa</th>
          <th>Typ</th>
          <th>Grubość</th>
          <th>U-value</th>
          <th>Cena/m²</th>
          <th>Opis</th>
          <th>Akcje</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="glass in glasses" :key="glass.id">
          <td>{{ glass.name }}</td>
          <td>{{ glass.type }}</td>
          <td>{{ glass.thickness }}mm</td>
          <td>{{ glass.u_value }}</td>
          <td>{{ glass.price_per_sqm }} zł</td>
          <td>{{ glass.description || '-' }}</td>
          <td>
            <button @click="editGlass(glass)" class="btn btn-secondary">Edytuj</button>
            <button @click="deleteGlass(glass.id)" class="btn btn-danger">Usuń</button>
          </td>
        </tr>
      </tbody>
    </table>

    <PaginationControls
      v-if="!loading && pagination.last_page > 1"
      :pagination-data="pagination"
      @page-change="handlePageChange"
      @per-page-change="handlePerPageChange"
    />

    <div v-if="showForm" class="modal">
      <div class="modal-content card">
        <h2>{{ editingGlass ? 'Edytuj szkło' : 'Dodaj nowe szkło' }}</h2>
        <form @submit.prevent="saveGlass">
          <div class="form-group">
            <label>Nazwa:</label>
            <input v-model="formData.name" required>
          </div>
          <div class="form-group">
            <label>Typ:</label>
            <input v-model="formData.type" required>
          </div>
          <div class="form-group">
            <label>Grubość (mm):</label>
            <input v-model.number="formData.thickness" type="number" required>
          </div>
          <div class="form-group">
            <label>U-value:</label>
            <input v-model.number="formData.u_value" type="number" step="0.01" required>
          </div>
          <div class="form-group">
            <label>Cena za m² (zł):</label>
            <input v-model.number="formData.price_per_sqm" type="number" step="0.01" required>
          </div>
          <div class="form-group">
            <label>Opis:</label>
            <textarea v-model="formData.description" rows="3"></textarea>
          </div>
          <div class="actions">
            <button type="submit" class="btn btn-primary">Zapisz</button>
            <button type="button" @click="closeForm" class="btn btn-secondary">Anuluj</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useToast } from '@/composables/useToast'
import { useConfirm } from '@/composables/useConfirm'
import SearchFilterBar from '@/components/SearchFilterBar.vue'
import PaginationControls from '@/components/PaginationControls.vue'
import LoadingSpinner from '@/components/LoadingSpinner.vue'
import { glassesAPI } from '../services/api'

const { success, error: showError } = useToast()
const { confirm } = useConfirm()

const glasses = ref([])
const loading = ref(false)
const showForm = ref(false)
const editingGlass = ref(null)

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
  from: 0,
  to: 0
})
const searchQuery = ref('')

const formData = ref({
  name: '',
  type: '',
  thickness: 0,
  u_value: 0,
  price_per_sqm: 0,
  description: ''
})

const loadGlasses = async (page = 1) => {
  loading.value = true
  try {
    const params = {
      page,
      per_page: pagination.value.per_page,
      search: searchQuery.value || undefined
    }
    const response = await glassesAPI.getAll(params)
    
    if (response.data.data) {
      glasses.value = response.data.data
      pagination.value = {
        current_page: response.data.current_page,
        last_page: response.data.last_page,
        per_page: response.data.per_page,
        total: response.data.total,
        from: response.data.from,
        to: response.data.to
      }
    } else {
      glasses.value = response.data
    }
  } catch (err) {
    showError('Nie udało się załadować szkieł: ' + err.message)
  } finally {
    loading.value = false
  }
}

const handleSearch = () => {
  loadGlasses(1)
}

const handlePageChange = (page) => {
  loadGlasses(page)
}

const handlePerPageChange = (perPage) => {
  pagination.value.per_page = perPage
  loadGlasses(1)
}

const saveGlass = async () => {
  loading.value = true
  try {
    if (editingGlass.value) {
      await glassesAPI.update(editingGlass.value.id, formData.value)
      success('Szkło zostało zaktualizowane')
    } else {
      await glassesAPI.create(formData.value)
      success('Szkło zostało dodane')
    }
    closeForm()
    loadGlasses(pagination.value.current_page)
  } catch (err) {
    showError('Nie udało się zapisać: ' + err.message)
  } finally {
    loading.value = false
  }
}

const editGlass = (glass) => {
  editingGlass.value = glass
  formData.value = { ...glass }
  showForm.value = true
}

const deleteGlass = async (id) => {
  try {
    const confirmed = await confirm({
      title: 'Usunąć szkło?',
      message: 'Czy na pewno chcesz usunąć ten typ szkła?',
      confirmText: 'Usuń',
      cancelText: 'Anuluj',
      type: 'danger'
    })
    
    if (!confirmed) return
    
    loading.value = true
    await glassesAPI.delete(id)
    success('Szkło zostało usunięte')
    loadGlasses(pagination.value.current_page)
  } catch (err) {
    if (err !== false) {
      showError('Nie udało się usunąć: ' + err.message)
    }
  } finally {
    loading.value = false
  }
}

const closeForm = () => {
  showForm.value = false
  editingGlass.value = null
  formData.value = {
    name: '',
    type: '',
    thickness: 0,
    u_value: 0,
    price_per_sqm: 0,
    description: ''
  }
}

onMounted(() => {
  loadGlasses()
})
</script>

<style scoped>
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.actions {
  display: flex;
  gap: 0.5rem;
}

.modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  max-width: 500px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
}
</style>

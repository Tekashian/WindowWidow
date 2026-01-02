<template>
  <div class="windows-view">
    <div class="page-header">
      <div>
        <h1>🪟 Produkty - Okna</h1>
        <p>Zarządzaj katalogiem produktów okiennych</p>
      </div>
      <button @click="showForm = true" class="btn btn-primary">
        ➕ Dodaj okno
      </button>
    </div>

    <SearchFilterBar 
      v-model="searchQuery"
      @update:modelValue="handleSearch"
      placeholder="Szukaj okien po nazwie, typie..."
    >
      <template #filters>
        <select v-model="typeFilter" @change="handleSearch" class="form-control">
          <option value="">Wszystkie typy</option>
          <option value="Uchylne">Uchylne</option>
          <option value="Rozwieralne">Rozwieralne</option>
          <option value="Uchylno-rozwieralne">Uchylno-rozwieralne</option>
          <option value="Stałe">Stałe</option>
        </select>
        
        <select v-model="sortBy" @change="handleSearch" class="form-control">
          <option value="created_at">Najnowsze</option>
          <option value="name">Nazwa A-Z</option>
          <option value="price">Cena rosnąco</option>
        </select>
      </template>
    </SearchFilterBar>

    <LoadingSpinner v-if="loading" size="large" message="Ładowanie okien..." />

    <div v-if="!loading" class="windows-grid">
      <div v-for="window in windows" :key="window.id" class="card window-card">
        <div class="window-header">
          <h3>{{ window.name }}</h3>
          <div class="status-badge" :class="`status-${window.status}`">
            {{ getStatusLabel(window.status) }}
          </div>
        </div>

        <div class="window-type">{{ window.type }}</div>

        <div class="window-specs">
          <div class="spec-item">
            <span class="icon">📏</span>
            <span>{{ window.width }} × {{ window.height }} mm</span>
          </div>
          <div class="spec-item">
            <span class="icon">🔲</span>
            <span>{{ window.profile?.name || 'Brak profilu' }}</span>
          </div>
          <div class="spec-item">
            <span class="icon">💎</span>
            <span>{{ window.glass?.name || 'Brak szkła' }}</span>
          </div>
        </div>

        <div class="window-footer">
          <div class="price">{{ window.price }} zł</div>
          <div class="actions">
            <button @click="editWindow(window)" class="btn btn-secondary btn-sm">✏️ Edytuj</button>
            <button @click="deleteWindow(window.id)" class="btn btn-danger btn-sm">🗑️ Usuń</button>
          </div>
        </div>
      </div>
    </div>

    <PaginationControls
      v-if="!loading && pagination.last_page > 1"
      :pagination-data="pagination"
      @page-change="handlePageChange"
      @per-page-change="handlePerPageChange"
    />

    <!-- Modal Form -->
    <Transition name="fade">
      <div v-if="showForm" class="modal-overlay" @click.self="closeForm">
        <div class="modal">
          <div class="modal-header">
            <h2>{{ editingWindow ? '✏️ Edytuj okno' : '➕ Dodaj nowe okno' }}</h2>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveWindow">
              <div class="form-group">
                <label>Nazwa *</label>
                <input v-model="formData.name" class="form-control" required placeholder="np. Okno PCV standard">
              </div>

              <div class="form-group">
                <label>Typ *</label>
                <select v-model="formData.type" class="form-control" required>
                  <option value="">Wybierz typ</option>
                  <option value="Uchylne">Uchylne</option>
                  <option value="Rozwieralne">Rozwieralne</option>
                  <option value="Uchylno-rozwieralne">Uchylno-rozwieralne</option>
                  <option value="Stałe">Stałe</option>
                </select>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label>Szerokość (mm) *</label>
                  <input v-model.number="formData.width" type="number" class="form-control" required>
                </div>
                <div class="form-group">
                  <label>Wysokość (mm) *</label>
                  <input v-model.number="formData.height" type="number" class="form-control" required>
                </div>
              </div>

              <div class="form-group">
                <label>Cena (zł) *</label>
                <input v-model.number="formData.price" type="number" step="0.01" class="form-control" required>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label>Profil</label>
                  <select v-model="formData.profile_id" class="form-control">
                    <option value="">Wybierz profil</option>
                    <option v-for="profile in profiles" :key="profile.id" :value="profile.id">
                      {{ profile.name }} - {{ profile.manufacturer }}
                    </option>
                  </select>
                </div>
                <div class="form-group">
                  <label>Szkło</label>
                  <select v-model="formData.glass_id" class="form-control">
                    <option value="">Wybierz szkło</option>
                    <option v-for="glass in glasses" :key="glass.id" :value="glass.id">
                      {{ glass.name }} ({{ glass.thickness }}mm)
                    </option>
                  </select>
                </div>
              </div>

              <div class="form-group">
                <label>Status</label>
                <select v-model="formData.status" class="form-control">
                  <option value="projekt">📝 Projekt</option>
                  <option value="w_produkcji">⚙️ W produkcji</option>
                  <option value="gotowe">✅ Gotowe</option>
                  <option value="wydane">📦 Wydane</option>
                </select>
              </div>

              <div class="modal-footer">
                <button type="button" @click="closeForm" class="btn btn-secondary">Anuluj</button>
                <button type="submit" class="btn btn-primary">
                  {{ editingWindow ? 'Zapisz zmiany' : 'Dodaj okno' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      <{ useToast } from '@/composables/useToast'
import { useConfirm } from '@/composables/useConfirm'
import SearchFilterBar from '@/components/SearchFilterBar.vue'
import PaginationControls from '@/components/PaginationControls.vue'
import LoadingSpinner from '@/components/LoadingSpinner.vue'
import api from '../services/api'

const { success, error: showError } = useToast()
const { confirm } = useConfirm()

const windows = ref([])
const profiles = ref([])
const glasses = ref([])
const loading = ref(false)
const showForm = ref(false)
const editingWindow = ref(null)

// Pagination & Filters
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
  from: 0,
  to: 0
})
const searchQuery = ref('')
const typeFilter = ref('')
const sortBy = ref('created_at')

const windows = ref([])
const profiles = ref([])
const glasses = ref([])
const loading = ref(false)
const error = ref(null)
const showForm = ref(false)
const editingWindow = ref(null)
const formData = ref({page = 1) => {
  loading.value = true
  try {
    const params = {
      page,
      per_page: pagination.value.per_page,
      search: searchQuery.value || undefined,
      type: typeFilter.value || undefined,
      sort_by: sortBy.value,
      sort_order: sortBy.value === 'price' ? 'asc' : 'desc'
    }
    
    const response = await api.get('/windows', { params })
    
    if (response.data.data) {
      windows.value = response.data.data
      pagination.value = {
        current_page: response.data.current_page,
        last_page: response.data.last_page,
        per_page: response.data.per_page,
        total: response.data.total,
        from: response.data.from,
        to: response.data.to
      }
    } else {
      windows.value = response.data
    }
  } catch (err) {
    showError('Nie udało się załadować okien: ' + err.message)
  } finally {
    loading.value = false
  }
}

const handleSearch = () => {
  loadWindows(1)
}

const handlePageChange = (page) => {
  loadWindows(page)
}

const handlePerPageChange = (perPage) => {
  pagination.value.per_page = perPage
  loadWindows(1)
const getStatusLabel = (status) => {
  const labels = {
    projekt: '📝 Projekt',
    w_produkcji: '⚙️ W produkcji',
    gotowe: '✅ Gotowe',
    wydane: '📦 Wydane'
  }
  return labels[status] || status
}loading.value = true
  try {
    if (editingWindow.value) {
      await api.put(`/windows/${editingWindow.value.id}`, formData.value)
      success('Okno zostało zaktualizowane')
    } else {
      await api.post('/windows', formData.value)
      success('Okno zostało dodane')
    }
    closeForm()
    loadWindows(pagination.value.current_page)
  } catch (err) {
    showError('Nie udało się zapisać: ' + err.message)
  } finally {
    loading.value = falsrr.message
  } finally {
    loading.value = false
  }
}

const loadProfiles = async () => {
  try {
    const response = await api.get('/profiles')
    profiles.value = response.data
  } catch (err) {
    console.error('Błąd ładowania profili:', err)
  }
}

cotry {
    const confirmed = await confirm({
      title: 'Usunąć okno?',
      message: 'Czy na pewno chcesz usunąć to okno? Tej operacji nie można cofnąć.',
      confirmText: 'Usuń',
      cancelText: 'Anuluj',
      type: 'danger'
    })
    
    if (!confirmed) return
    
    loading.value = true
    await api.delete(`/windows/${id}`)
    success('Okno zostało usunięte')
    loadWindows(pagination.value.current_page)
  } catch (err) {
    if (err !== false) {
      showError('Nie udało się usunąć: ' + err.message)
    }
  } finally {
    loading.value = false
}

const saveWindow = async () => {
  try {
    if (editingWindow.value) {
      await api.put(`/windows/${editingWindow.value.id}`, formData.value)
    } else {
      await api.post('/windows', formData.value)
    }
    closeForm()
    loadWindows()
  } catch (err) {
    error.value = 'Nie udało się zapisać: ' + err.message
  }
}

const editWindow = (window) => {
  editingWindow.value = window
  formData.value = {
    name: window.name,
    type: window.type,
    width: window.width,
    height: window.height,
    profile_id: window.profile_id || '',
    glass_id: window.glass_id || '',
    price: window.price,
    status: window.status || 'projekt'
  }
  showForm.value = true
}

const deleteWindow = async (id) => {
  if (confirm('Czy na pewno usunąć to okno?')) {
    try {
      await api.delete(`/windows/${id}`)
      loadWindows()
    } catch (err) {
      error.value = 'Nie udało się usunąć: ' + err.message
    }
  }
}

const closeForm = () => {
  showForm.value = false
  editingWindow.value = null
  formData.value = {
    name: '',
    type: '',
    width: 1000,
    height: 1200,
    profile_id: '',
    glass_id: '',
    price: 0,
    status: 'projekt'
  }
}

onMounted(() => {
  loadWindows()
  loadProfiles()
  loadGlasses()
})
</script>

<style scoped>
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.windows-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1.5rem;
}

.window-card {
  transition: all 0.3s ease;
}

.window-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-lg);
}

.window-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid var(--border);
}

.window-header h3 {
  margin: 0;
  color: var(--dark);
  font-size: 1.125rem;
}

.status-badge {
  padding: 0.375rem 0.75rem;
  border-radius: 8px;
  font-size: 0.75rem;
  font-weight: 600;
}

.status-projekt {
  background: #dbeafe;
  color: #1e40af;
}

.status-w_produkcji {
  background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
  color: #92400e;
}

.status-gotowe {
  background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
  color: #065f46;
}

.status-wydane {
  background: #e5e7eb;
  color: #374151;
}

.window-type {
  font-size: 0.875rem;
  color: #6b7280;
  margin-bottom: 1rem;
  font-weight: 500;
}

.window-specs {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.spec-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  color: var(--dark);
}

.spec-item .icon {
  font-size: 1.125rem;
}

.window-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 1rem;
  border-top: 1px solid var(--border);
}

.price {
  font-size: 1.25rem;
  font-weight: 700;
  background: var(--gradient-1);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.actions {
  display: flex;
  gap: 0.5rem;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  backdrop-filter: blur(4px);
}

.modal {
  background: white;
  border-radius: 20px;
  max-width: 700px;
  width: 90%;
  box-shadow: var(--shadow-xl);
  animation: slideUp 0.3s ease;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  padding: 1.5rem;
  border-bottom: 1px solid var(--border);
}

.modal-header h2 {
  margin: 0;
}

.modal-body {
  padding: 1.5rem;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.modal-footer {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  margin-top: 1.5rem;
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(50px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>

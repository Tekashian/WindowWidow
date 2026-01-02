<template>
  <div class="materials-view">
    <div class="page-header">
      <div>
        <h1>📦 Magazyn</h1>
        <p>Zarządzaj stanami magazynowymi materiałów i produktów</p>
      </div>
      <button @click="showAddModal = true" class="btn btn-primary">
        ➕ Dodaj materiał
      </button>
    </div>

    <!-- Tabs -->
    <div class="tabs">
      <button 
        @click="activeTab = 'materials'" 
        class="tab" 
        :class="{ active: activeTab === 'materials' }"
      >
        📦 Materiały
      </button>
      <button 
        @click="activeTab = 'windows'" 
        class="tab" 
        :class="{ active: activeTab === 'windows' }"
      >
        🪟 Produkty (Okna)
      </button>
    </div>

    <!-- Low Stock Alert -->
    <div v-if="activeTab === 'materials' && lowStockMaterials.length > 0" class="alert alert-warning">
      <strong>⚠️ Uwaga!</strong> {{ lowStockMaterials.length }} materiałów ma niski stan magazynowy
    </div>

    <div v-if="activeTab === 'windows' && lowStockWindows.length > 0" class="alert alert-warning">
      <strong>⚠️ Uwaga!</strong> {{ lowStockWindows.length }} produktów ma niski stan magazynowy
    </div>

    <div v-if="loading" class="loading">
      <div class="spinner"></div>
    </div>

    <!-- Materials Tab -->
    <div v-if="activeTab === 'materials'" class="materials-grid">
      <div v-for="material in materials" :key="material.id" class="material-card card">
        <div class="material-header">
          <div class="material-type-badge" :class="`type-${material.type}`">
            {{ getTypeLabel(material.type) }}
          </div>
          <div v-if="material.current_stock <= material.min_stock" class="low-stock-badge">
            ⚠️ Niski stan
          </div>
        </div>

        <h3>{{ material.name }}</h3>
        <p class="supplier">🏢 {{ material.supplier || 'Brak dostawcy' }}</p>

        <div class="stock-info">
          <div class="stock-bar-container">
            <div 
              class="stock-bar" 
              :style="{ 
                width: getStockPercentage(material) + '%',
                background: getStockColor(material)
              }"
            ></div>
          </div>
          <div class="stock-numbers">
            <span class="current-stock">{{ material.current_stock }} {{ material.unit }}</span>
            <span class="min-stock">Min: {{ material.min_stock }}</span>
          </div>
        </div>

        <div class="material-footer">
          <div class="price">{{ material.price_per_unit }} zł/{{ material.unit }}</div>
          <div class="actions">
            <button @click="openStockModal(material, 'add')" class="btn btn-success btn-icon" title="Dodaj">➕</button>
            <button @click="openStockModal(material, 'remove')" class="btn btn-danger btn-icon" title="Usuń">➖</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Windows Tab -->
    <div v-if="activeTab === 'windows'" class="materials-grid">
      <div v-for="window in windows" :key="window.id" class="material-card card window-card">
        <div class="window-image" v-if="window.image_url">
          <img :src="window.image_url" :alt="window.name" />
        </div>
        
        <div class="material-header">
          <div class="window-type-badge">
            {{ window.type }}
          </div>
          <div v-if="window.stock_quantity <= window.min_stock_level" class="low-stock-badge">
            ⚠️ Niski stan
          </div>
        </div>

        <h3>{{ window.name }}</h3>
        <p class="supplier">📐 {{ window.width }} × {{ window.height }} mm</p>
        <p class="window-description">{{ window.description?.substring(0, 100) }}...</p>

        <div class="stock-info">
          <div class="stock-bar-container">
            <div 
              class="stock-bar" 
              :style="{ 
                width: getWindowStockPercentage(window) + '%',
                background: getWindowStockColor(window)
              }"
            ></div>
          </div>
          <div class="stock-numbers">
            <span class="current-stock">{{ window.stock_quantity }} szt.</span>
            <span class="min-stock">Min: {{ window.min_stock_level }}</span>
          </div>
        </div>

        <div class="material-footer">
          <div class="price">{{ window.price }} zł</div>
          <div class="actions">
            <button @click="openWindowStockModal(window, 'add')" class="btn btn-success btn-icon" title="Przyjęcie">➕</button>
            <button @click="openWindowStockModal(window, 'subtract')" class="btn btn-danger btn-icon" title="Wydanie">➖</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Stock Modal -->
    <Transition name="fade">
      <div v-if="showStockModal" class="modal-overlay" @click.self="closeStockModal">
        <div class="modal">
          <div class="modal-header">
            <h2>{{ stockAction === 'add' ? '➕ Przyjęcie' : '➖ Wydanie' }} materiału</h2>
          </div>
          <div class="modal-body">
            <h3>{{ selectedMaterial?.name }}</h3>
            <p>Aktualny stan: {{ selectedMaterial?.current_stock }} {{ selectedMaterial?.unit }}</p>

            <form @submit.prevent="handleStockChange">
              <div class="form-group">
                <label>Ilość ({{ selectedMaterial?.unit }})</label>
                <input 
                  v-model.number="stockForm.quantity" 
                  type="number" 
                  step="0.01" 
                  class="form-control" 
                  required
                  min="0.01"
                />
              </div>

              <div class="form-group">
                <label>Powód</label>
                <input 
                  v-model="stockForm.reason" 
                  type="text" 
                  class="form-control"
                  :placeholder="stockAction === 'add' ? 'np. Dostawa od dostawcy' : 'np. Zużycie produkcyjne'"
                />
              </div>

              <div v-if="error" class="alert alert-error">
                {{ error }}
              </div>

              <div class="modal-footer">
                <button type="button" @click="closeStockModal" class="btn btn-secondary">Anuluj</button>
                <button type="submit" class="btn" :class="stockAction === 'add' ? 'btn-success' : 'btn-danger'">
                  {{ stockAction === 'add' ? 'Przyjmij' : 'Wydaj' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Window Stock Modal -->
    <Transition name="fade">
      <div v-if="showWindowStockModal" class="modal-overlay" @click.self="closeWindowStockModal">
        <div class="modal">
          <div class="modal-header">
            <h2>{{ windowStockAction === 'add' ? '➕ Przyjęcie' : '➖ Wydanie' }} produktu</h2>
          </div>
          <div class="modal-body">
            <h3>{{ selectedWindow?.name }}</h3>
            <p>Aktualny stan: {{ selectedWindow?.stock_quantity }} szt.</p>

            <form @submit.prevent="handleWindowStockChange">
              <div class="form-group">
                <label>Ilość (szt.)</label>
                <input 
                  v-model.number="windowStockForm.quantity" 
                  type="number" 
                  class="form-control" 
                  required
                  min="1"
                />
              </div>

              <div v-if="error" class="alert alert-error">
                {{ error }}
              </div>

              <div class="modal-footer">
                <button type="button" @click="closeWindowStockModal" class="btn btn-secondary">Anuluj</button>
                <button type="submit" class="btn" :class="windowStockAction === 'add' ? 'btn-success' : 'btn-danger'">
                  {{ windowStockAction === 'add' ? 'Przyjmij' : 'Wydaj' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useMaterialStore } from '../stores'
import api from '../services/api'

const materialStore = useMaterialStore()

const activeTab = ref('materials')
const materials = computed(() => materialStore.materials)
const lowStockMaterials = computed(() => materialStore.lowStockMaterials)
const loading = computed(() => materialStore.loading)

// Windows state
const windows = ref([])
const lowStockWindows = computed(() => windows.value.filter(w => w.stock_quantity <= w.min_stock_level))
const showWindowStockModal = ref(false)
const selectedWindow = ref(null)
const windowStockAction = ref('add')
const windowStockForm = ref({ quantity: 0 })

const showAddModal = ref(false)
const showStockModal = ref(false)
const selectedMaterial = ref(null)
const stockAction = ref('add')
const stockForm = ref({
  quantity: 0,
  reason: ''
})
const error = ref(null)

// Load windows
const loadWindows = async () => {
  try {
    const response = await api.get('/windows')
    windows.value = response.data
    console.log('Loaded windows:', windows.value)
  } catch (err) {
    console.error('Error loading windows:', err)
    console.error('Error response:', err.response?.data)
    error.value = 'Nie udało się załadować produktów: ' + (err.response?.data?.message || err.message)
  }
}

const getTypeLabel = (type) => {
  const labels = {
    profil: '🔲 Profil',
    szyba: '💎 Szyba',
    okucie: '🔧 Okucie',
    uszczelka: '⚫ Uszczelka',
    inne: '📦 Inne'
  }
  return labels[type] || type
}

const getStockPercentage = (material) => {
  if (material.min_stock === 0) return 100
  return Math.min((material.current_stock / material.min_stock) * 100, 100)
}

const getStockColor = (material) => {
  const percentage = getStockPercentage(material)
  if (percentage <= 50) return 'var(--gradient-2)'
  if (percentage <= 100) return 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)'
  return 'var(--gradient-4)'
}

const openStockModal = (material, action) => {
  selectedMaterial.value = material
  stockAction.value = action
  stockForm.value = { quantity: 0, reason: '' }
  error.value = null
  showStockModal.value = true
}

const closeStockModal = () => {
  showStockModal.value = false
  selectedMaterial.value = null
}

const handleStockChange = async () => {
  error.value = null
  
  const success = stockAction.value === 'add'
    ? await materialStore.addStock(selectedMaterial.value.id, stockForm.value.quantity, stockForm.value.reason)
    : await materialStore.removeStock(selectedMaterial.value.id, stockForm.value.quantity, stockForm.value.reason)

  if (success) {
    closeStockModal()
    materialStore.fetchLowStock()
  } else {
    error.value = materialStore.error
  }
}

// Windows functions
const getWindowStockPercentage = (window) => {
  if (window.min_stock_level === 0) return 100
  return Math.min((window.stock_quantity / (window.min_stock_level * 2)) * 100, 100)
}

const getWindowStockColor = (window) => {
  const percentage = getWindowStockPercentage(window)
  if (percentage <= 50) return 'var(--gradient-2)'
  if (percentage <= 100) return 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)'
  return 'var(--gradient-4)'
}

const openWindowStockModal = (window, action) => {
  selectedWindow.value = window
  windowStockAction.value = action
  windowStockForm.value = { quantity: 0 }
  error.value = null
  showWindowStockModal.value = true
}

const closeWindowStockModal = () => {
  showWindowStockModal.value = false
  selectedWindow.value = null
}

const handleWindowStockChange = async () => {
  error.value = null
  
  try {
    await api.post(`/windows/${selectedWindow.value.id}/update-stock`, {
      quantity: windowStockForm.value.quantity,
      operation: windowStockAction.value
    })
    
    await loadWindows()
    closeWindowStockModal()
  } catch (err) {
    error.value = err.response?.data?.message || 'Wystąpił błąd'
  }
}

onMounted(() => {
  materialStore.fetchMaterials()
  materialStore.fetchLowStock()
  loadWindows()
})
</script>

<style scoped>
.tabs {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
  border-bottom: 2px solid var(--border);
}

.tab {
  padding: 1rem 2rem;
  background: none;
  border: none;
  color: var(--gray-600);
  font-weight: 600;
  cursor: pointer;
  transition: all var(--transition-base);
  border-bottom: 3px solid transparent;
  margin-bottom: -2px;
}

.tab:hover {
  color: var(--primary);
}

.tab.active {
  color: var(--primary);
  border-bottom-color: var(--primary);
}

.window-type-badge {
  padding: 0.375rem 0.75rem;
  border-radius: 8px;
  font-size: 0.75rem;
  font-weight: 600;
  background: var(--gradient-primary);
  color: white;
}

.window-card {
  border-left: 4px solid var(--primary);
}

.window-image {
  width: 100%;
  height: 200px;
  overflow: hidden;
  border-radius: 8px;
  margin-bottom: 1rem;
  background: var(--gray-100);
  display: flex;
  align-items: center;
  justify-content: center;
}

.window-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.window-type-badge {
  padding: 0.375rem 0.75rem;
  border-radius: 8px;
  font-size: 0.75rem;
  font-weight: 600;
  background: linear-gradient(135deg, #00F5FF 0%, #7C3AED 100%);
  color: white;
}

.window-description {
  font-size: 0.8rem;
  color: var(--gray-600);
  line-height: 1.4;
  margin-bottom: 1rem;
  min-height: 40px;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  gap: 1rem;
  flex-wrap: wrap;
}

.page-header h1 {
  font-size: 1.75rem;
  margin: 0;
}

.page-header p {
  margin: 0.25rem 0 0 0;
  color: var(--gray-600);
  font-size: 0.9rem;
}

.materials-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1.5rem;
}

.material-card {
  position: relative;
  transition: all 0.3s ease;
}

.material-card:hover {
  transform: translateY(-4px);
}

.material-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 1rem;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.material-type-badge {
  padding: 0.375rem 0.75rem;
  border-radius: 8px;
  font-size: 0.75rem;
  font-weight: 600;
  white-space: nowrap;
}

.type-profil { background: #dbeafe; color: #1e40af; }
.type-szyba { background: #fce7f3; color: #be185d; }
.type-okucie { background: #fef3c7; color: #92400e; }
.type-uszczelka { background: #f3e8ff; color: #6b21a8; }
.type-inne { background: #e5e7eb; color: #374151; }

.low-stock-badge {
  padding: 0.375rem 0.75rem;
  border-radius: 8px;
  background: var(--warning);
  color: white;
  font-size: 0.75rem;
  font-weight: 600;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.6; }
}

.material-card h3 {
  margin-bottom: 0.5rem;
  color: var(--dark);
  font-size: 1.1rem;
}

.supplier {
  color: #6b7280;
  font-size: 0.875rem;
  margin-bottom: 1rem;
}

.stock-info {
  margin: 1.5rem 0;
}

.stock-bar-container {
  height: 12px;
  background: var(--border);
  border-radius: 6px;
  overflow: hidden;
  margin-bottom: 0.5rem;
}

.stock-bar {
  height: 100%;
  transition: width 0.5s ease;
  border-radius: 6px;
}

.stock-numbers {
  display: flex;
  justify-content: space-between;
  font-size: 0.875rem;
}

.current-stock {
  font-weight: 700;
  color: var(--dark);
}

.min-stock {
  color: #6b7280;
}

.material-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 1rem;
  border-top: 1px solid var(--border);
}

.price {
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--primary);
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
  max-width: 500px;
  width: 90%;
  box-shadow: var(--shadow-xl);
  animation: slideUp 0.3s ease;
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

/* Tablet Responsive */
@media (max-width: 1024px) {
  .materials-grid {
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.25rem;
  }

  .page-header {
    margin-bottom: 1.5rem;
  }

  .modal {
    max-width: 90%;
  }
}

/* Mobile Responsive */
@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    align-items: flex-start;
    margin-bottom: 1.5rem;
    gap: 1rem;
  }

  .page-header h1 {
    font-size: 1.5rem;
  }

  .page-header p {
    font-size: 0.85rem;
  }

  .page-header button {
    width: 100%;
    justify-content: center;
  }

  .tabs {
    overflow-x: auto;
    scrollbar-width: none;
    -ms-overflow-style: none;
  }

  .tabs::-webkit-scrollbar {
    display: none;
  }

  .tab {
    flex: 1;
    min-width: 140px;
    font-size: 0.9rem;
  }

  .materials-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }

  .material-card {
    padding: 1rem;
  }

  .material-card:hover {
    transform: none;
  }

  .window-image {
    height: 180px;
  }

  .material-footer {
    flex-direction: column;
    gap: 1rem;
    align-items: stretch;
  }

  .actions {
    width: 100%;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.5rem;
  }

  .btn-icon {
    width: 100%;
    min-height: 44px;
    font-size: 1.2rem;
  }

  .modal {
    max-width: 95%;
    margin: 1rem;
    max-height: 90vh;
    overflow-y: auto;
  }

  .modal-header,
  .modal-body {
    padding: 1.25rem;
  }

  .modal-footer {
    flex-direction: column-reverse;
  }

  .modal-footer button {
    width: 100%;
    justify-content: center;
    min-height: 48px;
  }

  .alert {
    padding: 0.875rem 1rem;
    font-size: 0.85rem;
  }
}

/* Small Mobile */
@media (max-width: 480px) {
  .page-header h1 {
    font-size: 1.35rem;
  }

  .page-header p {
    font-size: 0.8rem;
  }

  .tab {
    min-width: 120px;
    padding: 0.75rem 1rem;
    font-size: 0.85rem;
  }

  .material-card h3 {
    font-size: 1rem;
  }

  .supplier {
    font-size: 0.8rem;
  }

  .window-image {
    height: 160px;
  }

  .window-description {
    font-size: 0.75rem;
  }

  .stock-numbers {
    font-size: 0.8rem;
  }

  .price {
    font-size: 1rem;
  }

  .btn-icon {
    min-height: 48px;
    font-size: 1.3rem;
  }

  .form-group label {
    font-size: 0.9rem;
  }

  .form-control {
    font-size: 0.9rem;
    padding: 0.75rem;
  }

  textarea.form-control {
    min-height: 100px;
  }
}

/* Touch-friendly improvements */
@media (hover: none) and (pointer: coarse) {
  .btn {
    min-height: 48px;
    font-size: 1rem;
  }

  .btn-icon {
    min-width: 48px;
    min-height: 48px;
  }

  .tab {
    min-height: 48px;
  }

  .material-type-badge,
  .low-stock-badge,
  .window-type-badge {
    padding: 0.5rem 0.875rem;
    font-size: 0.8rem;
  }
}
</style>

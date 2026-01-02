<template>
  <div class="materials-view">
    <div class="page-header">
      <div>
        <h1>📦 Magazyn Materiałów</h1>
        <p>Zarządzaj stanami magazynowymi</p>
      </div>
      <button @click="showAddModal = true" class="btn btn-primary">
        ➕ Dodaj materiał
      </button>
    </div>

    <!-- Low Stock Alert -->
    <div v-if="lowStockMaterials.length > 0" class="alert alert-warning">
      <strong>⚠️ Uwaga!</strong> {{ lowStockMaterials.length }} materiałów ma niski stan magazynowy
    </div>

    <div v-if="loading" class="loading">
      <div class="spinner"></div>
    </div>

    <div v-else class="materials-grid">
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
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useMaterialStore } from '../stores'

const materialStore = useMaterialStore()

const materials = computed(() => materialStore.materials)
const lowStockMaterials = computed(() => materialStore.lowStockMaterials)
const loading = computed(() => materialStore.loading)

const showAddModal = ref(false)
const showStockModal = ref(false)
const selectedMaterial = ref(null)
const stockAction = ref('add')
const stockForm = ref({
  quantity: 0,
  reason: ''
})
const error = ref(null)

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

onMounted(() => {
  materialStore.fetchMaterials()
  materialStore.fetchLowStock()
})
</script>

<style scoped>
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
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

.material-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 1rem;
}

.material-type-badge {
  padding: 0.375rem 0.75rem;
  border-radius: 8px;
  font-size: 0.75rem;
  font-weight: 600;
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
</style>

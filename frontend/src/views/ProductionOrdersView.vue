<template>
  <div class="production-orders-view">
    <div class="page-header">
      <div>
        <h1>🏭 Zlecenia Produkcyjne</h1>
        <p>Zarządzaj procesem produkcji okien</p>
      </div>
      <button @click="showCreateModal = true" class="btn btn-primary">
        ➕ Nowe zlecenie
      </button>
    </div>

    <div v-if="loading" class="loading">
      <div class="spinner"></div>
    </div>

    <div v-else class="orders-list">
      <div v-for="order in orders" :key="order.id" class="order-card card">
        <div class="order-header">
          <div class="order-info">
            <h3>#{{ order.id }} - {{ order.window?.name || 'Usunięte okno' }}</h3>
            <p class="order-date">📅 {{ formatDate(order.created_at) }}</p>
          </div>
          <div class="order-status-badge" :class="`status-${order.status}`">
            {{ getStatusLabel(order.status) }}
          </div>
        </div>

        <div class="order-details">
          <div class="detail-item">
            <span class="label">Priorytet:</span>
            <span class="value priority" :class="`priority-${order.priority}`">
              {{ getPriorityLabel(order.priority) }}
            </span>
          </div>
          <div class="detail-item">
            <span class="label">Utworzył:</span>
            <span class="value">{{ order.created_by?.name || 'Nieznany' }}</span>
          </div>
          <div v-if="order.started_at" class="detail-item">
            <span class="label">Rozpoczęto:</span>
            <span class="value">{{ formatDate(order.started_at) }}</span>
          </div>
          <div v-if="order.completed_at" class="detail-item">
            <span class="label">Zakończono:</span>
            <span class="value">{{ formatDate(order.completed_at) }}</span>
          </div>
        </div>

        <div v-if="order.notes" class="order-notes">
          <strong>📝 Notatki:</strong> {{ order.notes }}
        </div>

        <div class="order-actions">
          <button 
            v-if="order.status === 'nowe'" 
            @click="startOrder(order.id)"
            class="btn btn-success"
          >
            ▶️ Rozpocznij produkcję
          </button>
          <button 
            v-if="order.status === 'w_trakcie'" 
            @click="completeOrder(order.id)"
            class="btn btn-primary"
          >
            ✅ Zakończ zlecenie
          </button>
          <button 
            v-if="order.status !== 'zakonczone' && order.status !== 'anulowane'" 
            @click="cancelOrder(order.id)"
            class="btn btn-danger btn-outline"
          >
            ❌ Anuluj
          </button>
        </div>
      </div>
    </div>

    <!-- Create Order Modal -->
    <Transition name="fade">
      <div v-if="showCreateModal" class="modal-overlay" @click.self="closeCreateModal">
        <div class="modal">
          <div class="modal-header">
            <h2>➕ Nowe zlecenie produkcyjne</h2>
          </div>
          <div class="modal-body">
            <form @submit.prevent="handleCreateOrder">
              <div class="form-group">
                <label>Okno *</label>
                <select v-model="createForm.window_id" class="form-control" required>
                  <option value="">Wybierz okno</option>
                  <option v-for="window in availableWindows" :key="window.id" :value="window.id">
                    {{ window.name }} ({{ window.width }}x{{ window.height }}mm)
                  </option>
                </select>
              </div>

              <div class="form-group">
                <label>Priorytet *</label>
                <select v-model="createForm.priority" class="form-control" required>
                  <option value="niski">🟢 Niski</option>
                  <option value="normalny" selected>🟡 Normalny</option>
                  <option value="wysoki">🟠 Wysoki</option>
                  <option value="pilny">🔴 Pilny</option>
                </select>
              </div>

              <div class="form-group">
                <label>Notatki</label>
                <textarea 
                  v-model="createForm.notes" 
                  class="form-control" 
                  rows="3"
                  placeholder="Dodatkowe informacje o zleceniu..."
                ></textarea>
              </div>

              <div v-if="error" class="alert alert-error">
                {{ error }}
              </div>

              <div v-if="validationWarning" class="alert alert-warning">
                ⚠️ {{ validationWarning }}
              </div>

              <div class="modal-footer">
                <button type="button" @click="closeCreateModal" class="btn btn-secondary">Anuluj</button>
                <button type="submit" class="btn btn-primary" :disabled="creating">
                  {{ creating ? 'Tworzenie...' : 'Utwórz zlecenie' }}
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
import { useProductionOrderStore } from '../stores'
import api from '../services/api'

const productionOrderStore = useProductionOrderStore()

const orders = computed(() => productionOrderStore.orders)
const loading = computed(() => productionOrderStore.loading)

const showCreateModal = ref(false)
const availableWindows = ref([])
const createForm = ref({
  window_id: '',
  priority: 'normalny',
  notes: ''
})
const error = ref(null)
const validationWarning = ref(null)
const creating = ref(false)

const getStatusLabel = (status) => {
  const labels = {
    nowe: '🆕 Nowe',
    w_trakcie: '⚙️ W trakcie',
    zakonczone: '✅ Zakończone',
    anulowane: '❌ Anulowane'
  }
  return labels[status] || status
}

const getPriorityLabel = (priority) => {
  const labels = {
    niski: '🟢 Niski',
    normalny: '🟡 Normalny',
    wysoki: '🟠 Wysoki',
    pilny: '🔴 Pilny'
  }
  return labels[priority] || priority
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleString('pl-PL', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const openCreateModal = async () => {
  try {
    // Pobierz dostępne okna
    const response = await api.get('/windows')
    availableWindows.value = response.data
    showCreateModal.value = true
    error.value = null
    validationWarning.value = null
  } catch (err) {
    console.error('Błąd pobierania okien:', err)
  }
}

const closeCreateModal = () => {
  showCreateModal.value = false
  createForm.value = {
    window_id: '',
    priority: 'normalny',
    notes: ''
  }
  error.value = null
  validationWarning.value = null
}

const handleCreateOrder = async () => {
  error.value = null
  validationWarning.value = null
  creating.value = true

  const success = await productionOrderStore.createOrder(createForm.value)

  if (success) {
    closeCreateModal()
  } else {
    error.value = productionOrderStore.error
    // Sprawdź czy to błąd walidacji materiałów
    if (error.value && error.value.includes('niewystarczające')) {
      validationWarning.value = 'System automatycznie sprawdził dostępność materiałów. ' + error.value
    }
  }

  creating.value = false
}

const startOrder = async (orderId) => {
  if (!confirm('Czy na pewno chcesz rozpocząć produkcję? Materiały zostaną automatycznie pobrane z magazynu.')) {
    return
  }

  const success = await productionOrderStore.startOrder(orderId)
  
  if (!success && productionOrderStore.error) {
    alert('Błąd: ' + productionOrderStore.error)
  }
}

const completeOrder = async (orderId) => {
  if (!confirm('Czy na pewno chcesz zakończyć to zlecenie?')) {
    return
  }

  await productionOrderStore.completeOrder(orderId)
}

const cancelOrder = async (orderId) => {
  if (!confirm('Czy na pewno chcesz anulować to zlecenie?')) {
    return
  }

  await productionOrderStore.cancelOrder(orderId)
}

onMounted(async () => {
  productionOrderStore.fetchOrders()
  await openCreateModal()
  closeCreateModal()
})

// Watch create modal
const watchCreateModal = () => {
  if (showCreateModal.value) {
    openCreateModal()
  }
}
</script>

<style scoped>
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.orders-list {
  display: grid;
  gap: 1.5rem;
}

.order-card {
  transition: all 0.3s ease;
}

.order-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-lg);
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid var(--border);
}

.order-info h3 {
  margin: 0 0 0.5rem 0;
  color: var(--dark);
}

.order-date {
  color: #6b7280;
  font-size: 0.875rem;
  margin: 0;
}

.order-status-badge {
  padding: 0.5rem 1rem;
  border-radius: 12px;
  font-weight: 600;
  font-size: 0.875rem;
}

.status-nowe {
  background: #dbeafe;
  color: #1e40af;
}

.status-w_trakcie {
  background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
  color: #92400e;
  animation: pulse 2s infinite;
}

.status-zakonczone {
  background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
  color: #065f46;
}

.status-anulowane {
  background: #fee2e2;
  color: #991b1b;
}

.order-details {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 1rem;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.detail-item .label {
  font-size: 0.75rem;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.detail-item .value {
  font-weight: 600;
  color: var(--dark);
}

.priority {
  padding: 0.25rem 0.5rem;
  border-radius: 6px;
  display: inline-block;
  width: fit-content;
}

.priority-niski {
  background: #d1fae5;
  color: #065f46;
}

.priority-normalny {
  background: #fef3c7;
  color: #92400e;
}

.priority-wysoki {
  background: #fed7aa;
  color: #9a3412;
}

.priority-pilny {
  background: #fee2e2;
  color: #991b1b;
  animation: pulse 2s infinite;
}

.order-notes {
  background: #f9fafb;
  padding: 1rem;
  border-radius: 12px;
  margin-bottom: 1rem;
  font-size: 0.875rem;
}

.order-actions {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
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
  max-width: 600px;
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

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.7;
  }
}
</style>

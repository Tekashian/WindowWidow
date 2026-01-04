<template>
  <div class="order-details-container">
    <!-- Header -->
    <div class="page-header">
      <button @click="$router.back()" class="back-btn">
        <svg class="back-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Powrót
      </button>
      
      <div v-if="order" class="header-content">
        <div class="header-info">
          <h1 class="page-title">{{ order.order_number || `Zlecenie #${order.id}` }}</h1>
          <div class="header-badges">
            <StatusBadge :status="order.status" />
            <PriorityIndicator :priority="order.priority" />
            <span v-if="isDelayed" class="delayed-badge">🚨 Opóźnione</span>
          </div>
        </div>

        <div class="header-actions">
          <button v-if="order.status === 'pending'" @click="showStartProductionModal = true" class="action-btn action-start">
            ▶️ Rozpocznij produkcję
          </button>
          <button @click="showUpdateStatusModal = true" class="action-btn action-update">
            🔄 Zmień status
          </button>
          <button @click="showReportIssueModal = true" class="action-btn action-report">
            ⚠️ Zgłoś problem
          </button>
        </div>
      </div>
    </div>

    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p class="loading-text">Ładowanie...</p>
    </div>

    <div v-else-if="error" class="error-state">
      <div class="error-message">⚠️ {{ error }}</div>
    </div>

    <div v-else-if="order" class="content-layout">
      <!-- Left Column: Order Info -->
      <div class="main-column">
        <!-- Basic Info -->
        <div class="info-card">
          <h2 class="card-title">📋 Informacje podstawowe</h2>
          <div class="info-grid">
            <div class="info-item">
              <div class="info-label">Ilość</div>
              <div class="info-value info-value-highlight">{{ order.quantity }}</div>
            </div>
            <div class="info-item">
              <div class="info-label">Typ źródła</div>
              <div class="info-value">{{ order.source_type === 'customer_order' ? 'Zamówienie klienta' : 'Uzupełnienie magazynu' }}</div>
            </div>
            <div class="info-item">
              <div class="info-label">Przypisany do</div>
              <div class="info-value">{{ order.assigned_user?.name || '-' }}</div>
            </div>
            <div class="info-item">
              <div class="info-label">Utworzone przez</div>
              <div class="info-value">{{ order.creator?.name || '-' }}</div>
            </div>
            <div class="info-item">
              <div class="info-label">Data utworzenia</div>
              <div class="info-value">{{ formatDate(order.created_at) }}</div>
            </div>
            <div class="info-item">
              <div class="info-label">Szacowany termin</div>
              <div :class="isDelayed ? 'info-value info-value-danger' : 'info-value'">
                {{ order.estimated_completion_at ? formatDate(order.estimated_completion_at) : '-' }}
              </div>
            </div>
          </div>
        </div>

        <!-- Timeline -->
        <div class="timeline-card">
          <h2 class="card-title">📜 Historia</h2>
          <TimelineItem :timeline="order.timeline || []" />
        </div>

        <!-- Batches -->
        <div v-if="order.batches && order.batches.length > 0" class="batches-card">
          <h2 class="card-title">📦 Partie produkcyjne</h2>
          <div class="batches-list">
            <div v-for="batch in order.batches" :key="batch.id" class="batch-item">
              <div class="batch-content">
                <div class="batch-info">
                  <div class="batch-number">{{ batch.batch_number }}</div>
                  <div class="batch-quantity">Ilość: <span class="quantity-value">{{ batch.quantity }}</span></div>
                  <StatusBadge :status="batch.status" class="batch-status" />
                </div>
                <div v-if="batch.quality_check_passed !== null" class="batch-quality">
                  <span v-if="batch.quality_check_passed" class="quality-passed">✅ Kontrola jakości przeszła</span>
                  <span v-else class="quality-failed">❌ Kontrola jakości niepomyślna</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Materials -->
        <div v-if="order.materials && order.materials.length > 0" class="materials-card">
          <h2 class="card-title">🧱 Materiały</h2>
          <div class="materials-list">
            <div v-for="material in order.materials" :key="material.id" class="material-item">
              <div class="material-content">
                <div class="material-info">
                  <div class="material-name">{{ material.material?.name || 'Materiał' }}</div>
                  <div class="material-quantity">
                    Wymagane: <span class="quantity-required">{{ material.quantity_required }}</span>
                    | Użyte: <span class="quantity-used">{{ material.quantity_used || 0 }}</span>
                  </div>
                </div>
                <div v-if="material.reserved_at" class="material-reserved">✅ Zarezerwowane</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Issues & Actions -->
      <div class="side-column">
        <!-- Issues -->
        <div class="issues-card">
          <h2 class="card-title card-title-danger">⚠️ Problemy</h2>
          <div v-if="order.issues && order.issues.length > 0" class="issues-list">
            <div v-for="issue in order.issues" :key="issue.id" :class="['issue-item', issue.severity === 'critical' ? 'issue-critical' : 'issue-high']">
              <div class="issue-header">
                <span :class="['issue-severity', issue.severity === 'critical' ? 'severity-critical' : 'severity-high']">
                  {{ issue.severity }}
                </span>
                <span class="issue-type">{{ issue.issue_type }}</span>
              </div>
              <div class="issue-description">{{ issue.description }}</div>
              <div v-if="issue.status !== 'resolved'" class="issue-status">Status: {{ issue.status }}</div>
            </div>
          </div>
          <div v-else class="issues-empty">Brak zgłoszonych problemów</div>
        </div>

        <!-- Deliveries -->
        <div v-if="order.deliveries && order.deliveries.length > 0" class="deliveries-card">
          <h2 class="card-title card-title-teal">🚚 Dostawy</h2>
          <div class="deliveries-list">
            <div v-for="delivery in order.deliveries" :key="delivery.id" class="delivery-item">
              <div class="delivery-number">{{ delivery.delivery_number }}</div>
              <StatusBadge :status="delivery.status" class="delivery-status" />
              <div class="delivery-date">{{ formatDate(delivery.expected_delivery_date) }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <div v-if="showStartProductionModal" class="modal-overlay" @click="showStartProductionModal = false">
      <div class="modal-content" @click.stop>
        <h3 class="modal-title">🚀 Rozpocznij produkcję</h3>
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">Szacowany czas produkcji (w godzinach) *</label>
            <input 
              v-model.number="startProductionForm.production_time_hours" 
              type="number" 
              min="1"
              class="form-input" 
              placeholder="np. 24"
            />
            <p class="form-hint">Ile godzin zajmie wyprodukowanie tego zlecenia?</p>
          </div>
          
          <div class="form-group">
            <label class="form-label">Data wysyłki na magazyn *</label>
            <input 
              v-model="startProductionForm.estimated_warehouse_delivery_date" 
              type="datetime-local"
              :min="minDeliveryDate"
              class="form-input"
            />
            <p class="form-hint">Kiedy produkt będzie gotowy do wysłania na magazyn?</p>
          </div>

          <div class="form-group">
            <label class="form-label">Notatki</label>
            <textarea 
              v-model="startProductionForm.notes" 
              class="form-textarea" 
              rows="3"
              placeholder="Opcjonalne uwagi dotyczące rozpoczęcia produkcji..."
            ></textarea>
          </div>

          <div class="modal-actions">
            <button @click="startProduction" class="modal-btn modal-btn-primary">🚀 Rozpocznij</button>
            <button @click="showStartProductionModal = false" class="modal-btn modal-btn-secondary">Anuluj</button>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showUpdateStatusModal" class="modal-overlay" @click="showUpdateStatusModal = false">
      <div class="modal-content" @click.stop>
        <h3 class="modal-title">Zmień status</h3>
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">Nowy status</label>
            <select v-model="updateStatusForm.status" class="form-select">
              <option value="in_progress">W produkcji</option>
              <option value="quality_check">Kontrola jakości</option>
              <option value="completed">Ukończone</option>
              <option value="on_hold">Wstrzymane</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Notatki</label>
            <textarea v-model="updateStatusForm.notes" class="form-textarea" rows="3"></textarea>
          </div>
          <div class="modal-actions">
            <button @click="updateStatus" class="modal-btn modal-btn-primary">Zapisz</button>
            <button @click="showUpdateStatusModal = false" class="modal-btn modal-btn-secondary">Anuluj</button>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showReportIssueModal" class="modal-overlay" @click="showReportIssueModal = false">
      <div class="modal-content" @click.stop>
        <h3 class="modal-title modal-title-danger">Zgłoś problem</h3>
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">Typ problemu</label>
            <select v-model="reportIssueForm.issue_type" class="form-select">
              <option value="material_shortage">Brak materiałów</option>
              <option value="equipment_failure">Awaria sprzętu</option>
              <option value="quality_defect">Wada jakościowa</option>
              <option value="other">Inne</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Priorytet</label>
            <select v-model="reportIssueForm.severity" class="form-select">
              <option value="low">Niski</option>
              <option value="medium">Średni</option>
              <option value="high">Wysoki</option>
              <option value="critical">Krytyczny</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Opis</label>
            <textarea v-model="reportIssueForm.description" class="form-textarea" rows="3"></textarea>
          </div>
          <div class="form-group">
            <label class="form-label">Wpływ</label>
            <select v-model="reportIssueForm.impact" class="form-select">
              <option value="none">Brak</option>
              <option value="minimal">Minimalny</option>
              <option value="moderate">Umiarkowany</option>
              <option value="severe">Poważny</option>
            </select>
          </div>
          <div class="modal-actions">
            <button @click="reportIssue" class="modal-btn modal-btn-danger">Zgłoś</button>
            <button @click="showReportIssueModal = false" class="modal-btn modal-btn-secondary">Anuluj</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useProductionStore } from '../../stores/productionStore';
import StatusBadge from './components/StatusBadge.vue';
import PriorityIndicator from './components/PriorityIndicator.vue';
import TimelineItem from './components/TimelineItem.vue';

const route = useRoute();
const productionStore = useProductionStore();

const order = computed(() => productionStore.currentOrder);
const loading = computed(() => productionStore.loading);
const error = computed(() => productionStore.error);

const showUpdateStatusModal = ref(false);
const showReportIssueModal = ref(false);
const showStartProductionModal = ref(false);

const startProductionForm = ref({
  production_time_hours: 24,
  estimated_warehouse_delivery_date: '',
  notes: ''
});

const updateStatusForm = ref({
  status: 'in_progress',
  notes: ''
});

const reportIssueForm = ref({
  issue_type: 'material_shortage',
  severity: 'medium',
  description: '',
  impact: 'moderate'
});

const minDeliveryDate = computed(() => {
  const now = new Date();
  now.setHours(now.getHours() + 1); // Minimum 1 hour from now
  return now.toISOString().slice(0, 16);
});

const isDelayed = computed(() => {
  if (!order.value?.estimated_completion_at) return false;
  const now = new Date();
  const estimated = new Date(order.value.estimated_completion_at);
  return estimated < now && !['completed', 'delivered', 'cancelled'].includes(order.value.status);
});

const formatDate = (dateString) => {
  if (!dateString) return '-';
  const date = new Date(dateString);
  return date.toLocaleDateString('pl-PL', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const startProduction = async () => {
  try {
    await productionStore.startProduction(route.params.id, startProductionForm.value);
    showStartProductionModal.value = false;
    startProductionForm.value = {
      production_time_hours: 24,
      estimated_warehouse_delivery_date: '',
      notes: ''
    };
    // Odśwież dane zlecenia
    await productionStore.fetchOrder(route.params.id);
  } catch (err) {
    console.error('Failed to start production:', err);
    
    // Wyświetl szczegółowy błąd użytkownikowi
    let errorMessage = 'Nie udało się rozpocząć produkcji.';
    
    if (err.response) {
      // Backend zwrócił błąd
      if (err.response.data && err.response.data.error) {
        errorMessage = err.response.data.error;
      } else if (err.response.data && err.response.data.message) {
        errorMessage = err.response.data.message;
      } else {
        errorMessage = `Błąd serwera (${err.response.status}): ${err.response.statusText}`;
      }
    } else if (err.request) {
      errorMessage = 'Brak odpowiedzi od serwera. Sprawdź połączenie internetowe.';
    } else {
      errorMessage = err.message || 'Wystąpił nieznany błąd.';
    }
    
    alert(`❌ ${errorMessage}`);
  }
};

const updateStatus = async () => {
  try {
    await productionStore.updateStatus(route.params.id, updateStatusForm.value);
    showUpdateStatusModal.value = false;
    updateStatusForm.value = { status: 'in_progress', notes: '' };
  } catch (err) {
    console.error('Failed to update status:', err);
  }
};

const reportIssue = async () => {
  try {
    await productionStore.reportIssue(route.params.id, reportIssueForm.value);
    showReportIssueModal.value = false;
    reportIssueForm.value = {
      issue_type: 'material_shortage',
      severity: 'medium',
      description: '',
      impact: 'moderate'
    };
  } catch (err) {
    console.error('Failed to report issue:', err);
  }
};

onMounted(() => {
  productionStore.fetchOrderDetails(route.params.id);
});
</script>

<style scoped>
.order-details-container {
  min-height: 100vh;
  background: linear-gradient(135deg, var(--darker), var(--dark));
  padding: 1rem;
}

@media (min-width: 768px) {
  .order-details-container {
    padding: 1.5rem;
  }
}

/* Page Header */
.page-header {
  margin-bottom: 1.5rem;
}

.back-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: var(--gray-400);
  background: none;
  border: none;
  cursor: pointer;
  margin-bottom: 1rem;
  transition: color var(--transition-base);
  padding: 0.5rem;
}

.back-btn:hover {
  color: var(--primary);
}

.back-icon {
  width: 1.25rem;
  height: 1.25rem;
}

.header-content {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

@media (min-width: 768px) {
  .header-content {
    flex-direction: row;
    justify-content: space-between;
    align-items: flex-start;
  }
}

.header-info {
  flex: 1;
}

.page-title {
  font-size: 1.875rem;
  font-weight: 700;
  background: linear-gradient(135deg, #00F5FF, #7C3AED);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin-bottom: 0.75rem;
}

.header-badges {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.delayed-badge {
  color: #F87171;
  font-size: 0.875rem;
}

.header-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.action-btn {
  padding: 0.5rem 1rem;
  color: white;
  border: none;
  border-radius: 0.5rem;
  font-weight: 600;
  cursor: pointer;
  transition: all var(--transition-base);
  font-size: 0.875rem;
}

.action-btn:hover {
  transform: translateY(-2px);
}

.action-start {
  background: #0891B2;
}

.action-start:hover {
  background: #06B6D4;
}

.action-update {
  background: #7C3AED;
}

.action-update:hover {
  background: #A855F7;
}

.action-report {
  background: #DC2626;
}

.action-report:hover {
  background: #EF4444;
}

/* Loading/Error States */
.loading-state,
.error-state {
  text-align: center;
  padding: 3rem 1rem;
}

.spinner {
  display: inline-block;
  width: 2rem;
  height: 2rem;
  border: 2px solid var(--gray-700);
  border-top-color: var(--primary);
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.loading-text {
  color: var(--gray-400);
  margin-top: 0.5rem;
}

.error-message {
  color: #F87171;
}

/* Content Layout */
.content-layout {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1.5rem;
}

@media (min-width: 1024px) {
  .content-layout {
    grid-template-columns: 2fr 1fr;
  }
}

.main-column,
.side-column {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

/* Cards */
.info-card,
.timeline-card,
.batches-card,
.materials-card,
.issues-card,
.deliveries-card {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  border: 1px solid var(--border);
  border-radius: 0.75rem;
  padding: 1.5rem;
}

.card-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--primary);
  margin-bottom: 1rem;
}

.card-title-danger {
  color: #F87171;
}

.card-title-teal {
  color: #14B8A6;
}

/* Info Card */
.info-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1rem;
}

@media (min-width: 768px) {
  .info-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

.info-item {
  display: flex;
  flex-direction: column;
}

.info-label {
  font-size: 0.875rem;
  color: var(--gray-400);
  margin-bottom: 0.25rem;
}

.info-value {
  color: white;
}

.info-value-highlight {
  font-size: 1.125rem;
  font-weight: 700;
}

.info-value-danger {
  color: #F87171;
}

/* Batches */
.batches-list,
.materials-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.batch-item,
.material-item {
  background: rgba(17, 24, 39, 0.5);
  border: 1px solid var(--border);
  border-radius: 0.5rem;
  padding: 1rem;
}

.batch-content,
.material-content {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.batch-number {
  font-family: 'Courier New', monospace;
  color: var(--primary);
  font-weight: 700;
  margin-bottom: 0.25rem;
}

.batch-quantity,
.material-quantity {
  font-size: 0.875rem;
  color: var(--gray-400);
}

.quantity-value {
  color: white;
}

.batch-status {
  margin-top: 0.5rem;
}

.quality-passed {
  color: #34D399;
  font-size: 0.875rem;
}

.quality-failed {
  color: #F87171;
  font-size: 0.875rem;
}

/* Materials */
.material-name {
  color: white;
  font-weight: 500;
  margin-bottom: 0.25rem;
}

.quantity-required {
  color: var(--primary);
}

.quantity-used {
  color: #A855F7;
}

.material-reserved {
  color: #34D399;
  font-size: 0.875rem;
}

/* Issues */
.issues-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.issue-item {
  border-radius: 0.5rem;
  padding: 0.75rem;
}

.issue-critical {
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.3);
}

.issue-high {
  background: rgba(251, 146, 60, 0.1);
  border: 1px solid rgba(251, 146, 60, 0.3);
}

.issue-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
}

.issue-severity {
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
}

.severity-critical {
  color: #F87171;
}

.severity-high {
  color: #FBBF24;
}

.issue-type {
  font-size: 0.75rem;
  color: var(--gray-400);
}

.issue-description {
  font-size: 0.875rem;
  color: var(--gray-300);
  margin-bottom: 0.5rem;
}

.issue-status {
  font-size: 0.75rem;
  color: #FDE68A;
}

.issues-empty {
  text-align: center;
  padding: 1rem;
  color: var(--gray-400);
}

/* Deliveries */
.deliveries-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.delivery-item {
  background: rgba(17, 24, 39, 0.5);
  border: 1px solid var(--border);
  border-radius: 0.5rem;
  padding: 0.75rem;
}

.delivery-number {
  font-family: 'Courier New', monospace;
  color: #14B8A6;
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
}

.delivery-status {
  margin-top: 0.5rem;
  margin-bottom: 0.5rem;
}

.delivery-date {
  font-size: 0.75rem;
  color: var(--gray-400);
}

/* Modals */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.7);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 50;
  padding: 1rem;
}

.modal-content {
  background: rgba(31, 41, 55, 1);
  border: 1px solid var(--border);
  border-radius: 0.75rem;
  padding: 1.5rem;
  max-width: 28rem;
  width: 100%;
}

.modal-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--primary);
  margin-bottom: 1rem;
}

.modal-title-danger {
  color: #F87171;
}

.modal-body {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-label {
  display: block;
  color: var(--gray-400);
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
}

.form-input,
.form-select,
.form-textarea {
  width: 100%;
  background: rgba(17, 24, 39, 1);
  border: 1px solid var(--border);
  color: white;
  border-radius: 0.5rem;
  padding: 0.5rem 0.75rem;
  transition: all var(--transition-base);
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
  outline: none;
  border-color: var(--primary);
}

.form-hint {
  font-size: 0.75rem;
  color: var(--gray-500);
  margin-top: 0.25rem;
}

.modal-actions {
  display: flex;
  gap: 0.5rem;
  margin-top: 0.5rem;
}

.modal-btn {
  flex: 1;
  padding: 0.5rem;
  color: white;
  border: none;
  border-radius: 0.5rem;
  font-weight: 600;
  cursor: pointer;
  transition: all var(--transition-base);
}

.modal-btn-primary {
  background: #0891B2;
}

.modal-btn-primary:hover {
  background: #06B6D4;
}

.modal-btn-secondary {
  background: var(--gray-700);
}

.modal-btn-secondary:hover {
  background: var(--gray-600);
}

.modal-btn-danger {
  background: #DC2626;
}

.modal-btn-danger:hover {
  background: #EF4444;
}
</style>

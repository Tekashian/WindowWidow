<template>
  <div class="orders-list-container">
    <!-- Header -->
    <div class="page-header">
      <div class="header-content">
        <h1 class="page-title">
          📋 Lista Zleceń Produkcyjnych
        </h1>
        <p class="page-subtitle">Zarządzaj wszystkimi zleceniami produkcyjnymi</p>
      </div>
      <button @click="$router.push('/production/orders/new')" class="new-order-btn">
        <svg class="btn-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Nowe zlecenie
      </button>
    </div>

    <!-- Filters -->
    <div class="filters-card">
      <div class="filters-grid">
        <!-- Status Filter -->
        <div class="filter-group">
          <label class="filter-label">Status</label>
          <select v-model="filters.status" @change="applyFilters" class="filter-select">
            <option value="">Wszystkie</option>
            <option value="pending">Oczekujące</option>
            <option value="materials_check">Sprawdzanie materiałów</option>
            <option value="materials_reserved">Materiały zarezerwowane</option>
            <option value="in_progress">W produkcji</option>
            <option value="quality_check">Kontrola jakości</option>
            <option value="completed">Ukończone</option>
            <option value="shipped_to_warehouse">Wysłano do magazynu</option>
            <option value="delivered">Dostarczone</option>
            <option value="on_hold">Wstrzymane</option>
            <option value="cancelled">Anulowane</option>
          </select>
        </div>

        <!-- Priority Filter -->
        <div class="filter-group">
          <label class="filter-label">Priorytet</label>
          <select v-model="filters.priority" @change="applyFilters" class="filter-select">
            <option value="">Wszystkie</option>
            <option value="low">Niski</option>
            <option value="normal">Normalny</option>
            <option value="high">Wysoki</option>
            <option value="urgent">Pilne</option>
          </select>
        </div>

        <!-- Show Filters -->
        <div class="filter-checkboxes">
          <label class="checkbox-label">
            <input type="checkbox" v-model="filters.in_progress" @change="applyFilters" class="filter-checkbox" />
            W produkcji
          </label>
        </div>

        <div class="filter-checkboxes">
          <label class="checkbox-label checkbox-label-danger">
            <input type="checkbox" v-model="filters.delayed" @change="applyFilters" class="filter-checkbox" />
            Opóźnione
          </label>
        </div>
      </div>

      <div class="filters-footer">
        <div class="results-count">
          Znaleziono: <span class="count-value">{{ orders.length }}</span> zleceń
        </div>
        <button @click="clearFilters" class="clear-filters-btn">
          Wyczyść filtry
        </button>
      </div>
    </div>

    <!-- Orders Table -->
    <div class="orders-table-card">
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p class="loading-text">Ładowanie zleceń...</p>
      </div>

      <div v-else-if="error" class="error-state">
        <div class="error-message">⚠️ {{ error }}</div>
        <button @click="loadOrders" class="retry-btn">
          Spróbuj ponownie
        </button>
      </div>

      <div v-else-if="orders.length === 0" class="empty-state">
        Brak zleceń spełniających kryteria
      </div>

      <!-- Desktop View -->
      <div v-else class="table-desktop">
        <table class="orders-table">
          <thead class="table-header">
            <tr class="header-row">
              <th class="header-cell">Numer zlecenia</th>
              <th class="header-cell">Status</th>
              <th class="header-cell">Priorytet</th>
              <th class="header-cell">Ilość</th>
              <th class="header-cell">Przypisany do</th>
              <th class="header-cell">Data utworzenia</th>
              <th class="header-cell">Termin</th>
              <th class="header-cell">Akcje</th>
            </tr>
          </thead>
          <tbody class="table-body">
            <tr
              v-for="order in orders"
              :key="order.id"
              @click="viewOrder(order.id)"
              class="table-row"
            >
              <td class="table-cell">
                <span class="order-number-cell">
                  {{ order.order_number || `#${order.id}` }}
                </span>
              </td>
              <td class="table-cell">
                <StatusBadge :status="order.status" />
              </td>
              <td class="table-cell">
                <PriorityIndicator :priority="order.priority" />
              </td>
              <td class="table-cell cell-quantity">
                {{ order.quantity }}
              </td>
              <td class="table-cell cell-user">
                {{ order.assigned_user?.name || '-' }}
              </td>
              <td class="table-cell cell-date">
                {{ formatDate(order.created_at) }}
              </td>
              <td class="table-cell cell-date">
                <span
                  v-if="order.estimated_completion_at"
                  :class="isDelayed(order) ? 'delayed-date' : ''"
                >
                  {{ formatDate(order.estimated_completion_at) }}
                </span>
                <span v-else class="no-date">-</span>
              </td>
              <td class="table-cell">
                <button
                  @click.stop="viewOrder(order.id)"
                  class="view-btn"
                  title="Zobacz szczegóły"
                >
                  <svg class="view-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                  </svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Mobile View -->
      <div class="mobile-list">
        <div
          v-for="order in orders"
          :key="order.id"
          @click="viewOrder(order.id)"
          class="mobile-order-item"
        >
          <div class="mobile-header">
            <span class="mobile-order-number">
              {{ order.order_number || `#${order.id}` }}
            </span>
            <PriorityIndicator :priority="order.priority" />
          </div>
          <StatusBadge :status="order.status" class="mobile-status" />
          <div class="mobile-details">
            <div class="mobile-detail-item">Ilość: <span class="detail-value">{{ order.quantity }}</span></div>
            <div v-if="order.assigned_user" class="mobile-detail-item">
              Przypisany: <span class="detail-value">{{ order.assigned_user.name }}</span>
            </div>
            <div class="mobile-detail-item">Data: {{ formatDate(order.created_at) }}</div>
            <div v-if="order.estimated_completion_at" :class="isDelayed(order) ? 'delayed-date' : 'mobile-detail-item'">
              Termin: {{ formatDate(order.estimated_completion_at) }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useProductionStore } from '../../stores/productionStore';
import StatusBadge from './components/StatusBadge.vue';
import PriorityIndicator from './components/PriorityIndicator.vue';

const router = useRouter();
const productionStore = useProductionStore();

const filters = ref({
  status: '',
  priority: '',
  in_progress: false,
  delayed: false
});

const orders = computed(() => productionStore.orders);
const loading = computed(() => productionStore.loading);
const error = computed(() => productionStore.error);

const formatDate = (dateString) => {
  if (!dateString) return '-';
  const date = new Date(dateString);
  return date.toLocaleDateString('pl-PL', { 
    day: '2-digit', 
    month: '2-digit', 
    year: 'numeric'
  });
};

const isDelayed = (order) => {
  if (!order.estimated_completion_at) return false;
  const now = new Date();
  const estimated = new Date(order.estimated_completion_at);
  return estimated < now && !['completed', 'delivered', 'cancelled'].includes(order.status);
};

const applyFilters = async () => {
  await loadOrders();
};

const clearFilters = () => {
  filters.value = {
    status: '',
    priority: '',
    in_progress: false,
    delayed: false
  };
  loadOrders();
};

const loadOrders = async () => {
  const params = {};
  if (filters.value.status) params.status = filters.value.status;
  if (filters.value.priority) params.priority = filters.value.priority;
  if (filters.value.in_progress) params.in_progress = true;
  if (filters.value.delayed) params.delayed = true;

  await productionStore.fetchOrders(params);
};

const viewOrder = (id) => {
  router.push(`/production/orders/${id}`);
};

onMounted(() => {
  loadOrders();
});
</script>

<style scoped>
.orders-list-container {
  min-height: 100vh;
  background: linear-gradient(135deg, var(--darker), var(--dark));
  padding: 1rem;
}

@media (min-width: 768px) {
  .orders-list-container {
    padding: 1.5rem;
  }
}

/* Page Header */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.header-content {
  flex: 1;
  min-width: 200px;
}

.page-title {
  font-size: 1.875rem;
  font-weight: 700;
  background: linear-gradient(135deg, #00F5FF, #7C3AED);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin-bottom: 0.5rem;
}

.page-subtitle {
  color: var(--gray-400);
  font-size: 0.95rem;
}

.new-order-btn {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.625rem 1rem;
  background: linear-gradient(135deg, #0891B2, #06B6D4);
  color: white;
  border: none;
  border-radius: 0.5rem;
  font-weight: 700;
  cursor: pointer;
  transition: all var(--transition-base);
}

.new-order-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 0 20px rgba(6, 182, 212, 0.4);
}

.btn-icon-sm {
  width: 1.25rem;
  height: 1.25rem;
}

/* Filters Card */
.filters-card {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  border: 1px solid var(--border);
  border-radius: 0.75rem;
  padding: 1rem;
  margin-bottom: 1.5rem;
}

.filters-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1rem;
  margin-bottom: 1rem;
}

@media (min-width: 768px) {
  .filters-grid {
    grid-template-columns: repeat(4, 1fr);
  }
}

.filter-group {
  display: flex;
  flex-direction: column;
}

.filter-label {
  display: block;
  color: var(--gray-400);
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
}

.filter-select {
  width: 100%;
  background: rgba(17, 24, 39, 1);
  border: 1px solid var(--border);
  color: white;
  border-radius: 0.5rem;
  padding: 0.5rem 0.75rem;
  font-size: 0.9375rem;
  transition: all var(--transition-base);
}

.filter-select:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(0, 245, 255, 0.1);
}

.filter-checkboxes {
  display: flex;
  align-items: flex-end;
}

.checkbox-label {
  display: flex;
  align-items: center;
  color: var(--gray-400);
  cursor: pointer;
  transition: color var(--transition-base);
}

.checkbox-label:hover {
  color: var(--primary);
}

.checkbox-label-danger:hover {
  color: #F87171;
}

.filter-checkbox {
  margin-right: 0.5rem;
  width: 1.125rem;
  height: 1.125rem;
  border-radius: 0.25rem;
  background: rgba(17, 24, 39, 1);
  border: 1px solid var(--border);
  cursor: pointer;
}

.filters-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.results-count {
  font-size: 0.875rem;
  color: var(--gray-400);
}

.count-value {
  color: var(--primary);
  font-weight: 700;
}

.clear-filters-btn {
  color: var(--gray-400);
  background: none;
  border: none;
  font-size: 0.875rem;
  text-decoration: underline;
  cursor: pointer;
  transition: color var(--transition-base);
}

.clear-filters-btn:hover {
  color: var(--primary);
}

/* Orders Table Card */
.orders-table-card {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  border: 1px solid var(--border);
  border-radius: 0.75rem;
  overflow: hidden;
}

/* Loading/Error/Empty States */
.loading-state,
.error-state,
.empty-state {
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
  to { transform: rotate(360deg); }
}

.loading-text {
  color: var(--gray-400);
  margin-top: 0.5rem;
}

.error-message {
  color: #F87171;
  margin-bottom: 0.5rem;
}

.retry-btn {
  color: var(--primary);
  background: none;
  border: none;
  cursor: pointer;
  text-decoration: underline;
  transition: color var(--transition-base);
}

.retry-btn:hover {
  color: var(--primary-dark);
}

.empty-state {
  color: var(--gray-400);
}

/* Desktop Table */
.table-desktop {
  display: none;
  overflow-x: auto;
}

@media (min-width: 768px) {
  .table-desktop {
    display: block;
  }
}

.orders-table {
  width: 100%;
  border-collapse: collapse;
}

.table-header {
  background: rgba(17, 24, 39, 0.5);
}

.header-row {
  text-align: left;
  color: var(--gray-400);
  font-size: 0.875rem;
}

.header-cell {
  padding: 1rem 1.5rem;
  font-weight: 600;
}

.table-body {
  background: transparent;
}

.table-row {
  cursor: pointer;
  transition: background var(--transition-fast);
  border-bottom: 1px solid var(--border);
}

.table-row:hover {
  background: rgba(17, 24, 39, 0.5);
}

.table-row:last-child {
  border-bottom: none;
}

.table-cell {
  padding: 1rem 1.5rem;
  color: var(--gray-300);
}

.order-number-cell {
  font-family: 'Courier New', monospace;
  color: var(--primary);
  font-weight: 700;
}

.cell-quantity {
  color: white;
}

.cell-user,
.cell-date {
  font-size: 0.875rem;
}

.delayed-date {
  color: #F87171;
}

.no-date {
  color: var(--gray-500);
}

.view-btn {
  background: none;
  border: none;
  color: var(--primary);
  cursor: pointer;
  padding: 0.25rem;
  transition: color var(--transition-base);
}

.view-btn:hover {
  color: var(--primary-dark);
}

.view-icon {
  width: 1.25rem;
  height: 1.25rem;
}

/* Mobile View */
.mobile-list {
  display: block;
}

@media (min-width: 768px) {
  .mobile-list {
    display: none;
  }
}

.mobile-order-item {
  padding: 1rem;
  cursor: pointer;
  transition: background var(--transition-fast);
  border-bottom: 1px solid var(--border);
}

.mobile-order-item:hover {
  background: rgba(17, 24, 39, 0.5);
}

.mobile-order-item:last-child {
  border-bottom: none;
}

.mobile-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 0.5rem;
}

.mobile-order-number {
  font-family: 'Courier New', monospace;
  color: var(--primary);
  font-weight: 700;
}

.mobile-status {
  margin-bottom: 0.5rem;
}

.mobile-details {
  font-size: 0.875rem;
  color: var(--gray-400);
}

.mobile-detail-item {
  margin-bottom: 0.25rem;
}

.detail-value {
  color: white;
}
</style>

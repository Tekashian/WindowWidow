<template>
  <div class="dashboard-container">
    <!-- Header -->
    <div class="dashboard-header">
      <h1 class="dashboard-title">
        🏭 Panel Produkcji
      </h1>
      <p class="dashboard-subtitle">Zarządzanie zleceniami produkcyjnymi WindowWidow</p>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
      <!-- Total Orders -->
      <div class="stat-card">
        <div class="stat-label">Wszystkie</div>
        <div class="stat-value">{{ statistics.total_orders }}</div>
      </div>

      <!-- Pending -->
      <div class="stat-card stat-blue">
        <div class="stat-label">Oczekujące</div>
        <div class="stat-value">{{ statistics.pending }}</div>
      </div>

      <!-- In Progress -->
      <div class="stat-card stat-purple">
        <div class="stat-label">W produkcji</div>
        <div class="stat-value">{{ statistics.in_progress }}</div>
      </div>

      <!-- Completed -->
      <div class="stat-card stat-green">
        <div class="stat-label">Ukończone</div>
        <div class="stat-value">{{ statistics.completed }}</div>
      </div>

      <!-- Delayed -->
      <div class="stat-card stat-orange">
        <div class="stat-label">Opóźnione</div>
        <div class="stat-value">{{ statistics.delayed }}</div>
      </div>

      <!-- On Hold -->
      <div class="stat-card stat-yellow">
        <div class="stat-label">Wstrzymane</div>
        <div class="stat-value">{{ statistics.on_hold }}</div>
      </div>

      <!-- Critical Issues -->
      <div class="stat-card stat-red stat-critical">
        <div class="stat-label">Krytyczne</div>
        <div class="stat-value">{{ statistics.critical_issues }}</div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="actions-grid">
      <button @click="$router.push('/production/orders/new')" class="action-btn action-btn-primary">
        <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Nowe zlecenie
      </button>

      <button @click="$router.push('/production/orders')" class="action-btn action-btn-secondary">
        <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        Lista zleceń
      </button>

      <button @click="$router.push('/production/issues')" class="action-btn action-btn-danger">
        <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        Problemy ({{ statistics.critical_issues }})
      </button>
    </div>

    <!-- Recent Orders -->
    <div class="content-card">
      <div class="card-header">
        <h2 class="card-title">📋 Ostatnie zlecenia</h2>
        <button @click="refreshData" :disabled="loading" class="refresh-btn">
          <svg :class="{ 'spinning': loading }" class="refresh-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
          </svg>
        </button>
      </div>

      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p class="loading-text">Ładowanie...</p>
      </div>

      <div v-else-if="error" class="error-state">
        <div class="error-message">⚠️ {{ error }}</div>
        <button @click="refreshData" class="retry-btn">Spróbuj ponownie</button>
      </div>

      <div v-else-if="orders.length === 0" class="empty-state">
        Brak zleceń do wyświetlenia
      </div>

      <div v-else class="orders-list">
        <div v-for="order in recentOrders" :key="order.id" @click="$router.push(`/production/orders/${order.id}`)" class="order-item">
          <div class="order-content">
            <div class="order-main">
              <div class="order-badges">
                <span class="order-number">{{ order.order_number || `#${order.id}` }}</span>
                <StatusBadge :status="order.status" />
                <PriorityIndicator :priority="order.priority" />
              </div>
              <div class="order-details">
                Ilość: <span class="detail-value">{{ order.quantity }}</span>
                <span v-if="order.windows"> | {{ order.windows.name }}</span>
              </div>
            </div>

            <div class="order-meta">
              <div v-if="order.assigned_user" class="meta-item">👤 {{ order.assigned_user.name }}</div>
              <div class="meta-item">📅 {{ formatDate(order.created_at) }}</div>
            </div>
          </div>
        </div>
      </div>

      <div v-if="orders.length > 5" class="card-footer">
        <button @click="$router.push('/production/orders')" class="view-all-btn">
          Zobacz wszystkie →
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useProductionStore } from '../../stores/productionStore';
import StatusBadge from './components/StatusBadge.vue';
import PriorityIndicator from './components/PriorityIndicator.vue';

const productionStore = useProductionStore();

const orders = computed(() => productionStore.orders);
const statistics = computed(() => productionStore.statistics);
const loading = computed(() => productionStore.loading);
const error = computed(() => productionStore.error);

const recentOrders = computed(() => orders.value.slice(0, 5));

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

const refreshData = async () => {
  try {
    await Promise.all([
      productionStore.fetchOrders(),
      productionStore.fetchStatistics()
    ]);
  } catch (err) {
    console.error('Failed to refresh data:', err);
  }
};

onMounted(() => {
  refreshData();
});
</script>

<style scoped>
.dashboard-container {
  min-height: 100vh;
  background: linear-gradient(135deg, var(--darker), var(--dark));
  padding: 1rem;
}

@media (min-width: 768px) {
  .dashboard-container {
    padding: 1.5rem;
  }
}

.dashboard-header {
  margin-bottom: 1.5rem;
}

.dashboard-title {
  font-size: 2rem;
  font-weight: 700;
  background: linear-gradient(135deg, #00F5FF, #7C3AED);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin-bottom: 0.5rem;
}

@media (min-width: 768px) {
  .dashboard-title {
    font-size: 2.5rem;
  }
}

.dashboard-subtitle {
  color: var(--gray-400);
  font-size: 0.95rem;
}

/* Statistics Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
  margin-bottom: 1.5rem;
}

@media (min-width: 768px) {
  .stats-grid {
    grid-template-columns: repeat(4, 1fr);
  }
}

@media (min-width: 1024px) {
  .stats-grid {
    grid-template-columns: repeat(7, 1fr);
  }
}

.stat-card {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  border: 1px solid var(--border);
  border-radius: 0.75rem;
  padding: 1rem;
  transition: all var(--transition-base);
}

.stat-card:hover {
  border-color: var(--primary);
  transform: translateY(-2px);
}

.stat-label {
  font-size: 0.875rem;
  color: var(--gray-400);
  margin-bottom: 0.5rem;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: white;
}

.stat-blue .stat-value { color: #60A5FA; }
.stat-purple { background: rgba(124, 58, 237, 0.1); border-color: rgba(124, 58, 237, 0.3); }
.stat-purple .stat-label { color: #C4B5FD; }
.stat-purple .stat-value { color: #A78BFA; }
.stat-purple:hover { border-color: #7C3AED; }

.stat-green { background: rgba(16, 185, 129, 0.1); border-color: rgba(16, 185, 129, 0.3); }
.stat-green .stat-label { color: #6EE7B7; }
.stat-green .stat-value { color: #34D399; }
.stat-green:hover { border-color: #10B981; }

.stat-orange { background: rgba(251, 146, 60, 0.1); border-color: rgba(251, 146, 60, 0.3); }
.stat-orange .stat-label { color: #FCD34D; }
.stat-orange .stat-value { color: #FBBF24; }
.stat-orange:hover { border-color: #F59E0B; }

.stat-yellow { background: rgba(234, 179, 8, 0.1); border-color: rgba(234, 179, 8, 0.3); }
.stat-yellow .stat-label { color: #FDE68A; }
.stat-yellow .stat-value { color: #FBBF24; }
.stat-yellow:hover { border-color: #EAB308; }

.stat-red { background: rgba(239, 68, 68, 0.1); border-color: rgba(239, 68, 68, 0.3); }
.stat-red .stat-label { color: #FCA5A5; }
.stat-red .stat-value { color: #F87171; }
.stat-red:hover { border-color: #EF4444; }

.stat-critical {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.7; }
}

/* Actions Grid */
.actions-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

@media (min-width: 768px) {
  .actions-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

.action-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 1rem 1.5rem;
  font-weight: 700;
  color: white;
  border: none;
  border-radius: 0.75rem;
  cursor: pointer;
  transition: all var(--transition-base);
  box-shadow: var(--shadow-lg);
}

.action-btn:hover {
  transform: translateY(-2px) scale(1.05);
}

.action-btn-primary {
  background: linear-gradient(135deg, #0891B2, #06B6D4);
}

.action-btn-primary:hover {
  box-shadow: 0 0 20px rgba(6, 182, 212, 0.4);
}

.action-btn-secondary {
  background: linear-gradient(135deg, #7C3AED, #A855F7);
}

.action-btn-secondary:hover {
  box-shadow: 0 0 20px rgba(168, 85, 247, 0.4);
}

.action-btn-danger {
  background: linear-gradient(135deg, #DC2626, #EF4444);
}

.action-btn-danger:hover {
  box-shadow: 0 0 20px rgba(239, 68, 68, 0.4);
}

.btn-icon {
  width: 1.5rem;
  height: 1.5rem;
}

/* Content Card */
.content-card {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  border: 1px solid var(--border);
  border-radius: 0.75rem;
  padding: 1.5rem;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.card-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--primary);
}

.refresh-btn {
  background: none;
  border: none;
  color: var(--gray-400);
  cursor: pointer;
  transition: color var(--transition-base);
  padding: 0.5rem;
}

.refresh-btn:hover {
  color: var(--primary);
}

.refresh-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.refresh-icon {
  width: 1.25rem;
  height: 1.25rem;
}

.spinning {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Loading/Error/Empty States */
.loading-state,
.error-state,
.empty-state {
  text-align: center;
  padding: 2rem 0;
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

.loading-text {
  color: var(--gray-400);
  margin-top: 0.5rem;
}

.error-message {
  color: #F87171;
  margin-bottom: 0.5rem;
}

.retry-btn,
.view-all-btn {
  color: var(--primary);
  background: none;
  border: none;
  cursor: pointer;
  text-decoration: underline;
  transition: color var(--transition-base);
  padding: 0.5rem;
}

.retry-btn:hover,
.view-all-btn:hover {
  color: var(--primary-dark);
}

.empty-state {
  color: var(--gray-400);
}

/* Orders List */
.orders-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.order-item {
  background: rgba(17, 24, 39, 0.5);
  border: 1px solid var(--border);
  border-radius: 0.5rem;
  padding: 1rem;
  cursor: pointer;
  transition: all var(--transition-base);
}

.order-item:hover {
  border-color: var(--primary);
  background: rgba(17, 24, 39, 0.7);
}

.order-content {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

@media (min-width: 768px) {
  .order-content {
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
  }
}

.order-main {
  flex: 1;
}

.order-badges {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
  flex-wrap: wrap;
}

.order-number {
  font-family: 'Courier New', monospace;
  color: var(--primary);
  font-weight: 700;
}

.order-details {
  color: var(--gray-400);
  font-size: 0.875rem;
}

.detail-value {
  color: white;
}

.order-meta {
  font-size: 0.875rem;
  color: var(--gray-400);
}

.meta-item {
  margin-bottom: 0.25rem;
}

.card-footer {
  margin-top: 1rem;
  text-align: center;
}

/* Responsive adjustments */
@media (max-width: 767px) {
  .action-btn {
    width: 100%;
  }
}
</style>

<template>
  <div class="dashboard">
    <div class="dashboard-header">
      <div>
        <h1>Production Dashboard</h1>
        <p class="subtitle">Real-time production monitoring and analytics</p>
      </div>
      <button @click="refreshData" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"/>
        </svg>
        <span>Refresh</span>
      </button>
    </div>

    <div v-if="loading" class="loading">
      <div class="spinner"></div>
    </div>

    <div v-else-if="statistics" class="dashboard-content">
      <!-- Stats Cards -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-header">
            <div class="stat-icon primary">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                <rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
              </svg>
            </div>
            <span class="stat-label">Total Orders</span>
          </div>
          <div class="stat-value">{{ statistics.production_orders.total }}</div>
        </div>

        <div class="stat-card">
          <div class="stat-header">
            <div class="stat-icon success">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="20 6 9 17 4 12"/>
              </svg>
            </div>
            <span class="stat-label">In Production</span>
          </div>
          <div class="stat-value">{{ statistics.production_orders.w_trakcie }}</div>
        </div>

        <div class="stat-card">
          <div class="stat-header">
            <div class="stat-icon warning">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
              </svg>
            </div>
            <span class="stat-label">Completed Today</span>
          </div>
          <div class="stat-value">{{ statistics.production_orders.zakonczone }}</div>
        </div>

        <div class="stat-card">
          <div class="stat-header">
            <div class="stat-icon secondary">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                <line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="21"/>
              </svg>
            </div>
            <span class="stat-label">Products</span>
          </div>
          <div class="stat-value">{{ statistics.windows.total }}</div>
        </div>
      </div>

      <!-- Windows Status -->
      <div class="card">
        <h2>Windows Production Status</h2>
        <div class="progress-section">
          <div class="progress-item">
            <div class="progress-label">
              <span>Design Phase</span>
              <strong>{{ statistics.windows.projekt }} items</strong>
            </div>
            <div class="progress-bar">
              <div class="progress-fill primary" :style="{ width: getPercentage(statistics.windows.projekt, statistics.windows.total) + '%' }"></div>
            </div>
          </div>

          <div class="progress-item">
            <div class="progress-label">
              <span>In Production</span>
              <strong>{{ statistics.windows.w_produkcji }} items</strong>
            </div>
            <div class="progress-bar">
              <div class="progress-fill success" :style="{ width: getPercentage(statistics.windows.w_produkcji, statistics.windows.total) + '%' }"></div>
            </div>
          </div>

          <div class="progress-item">
            <div class="progress-label">
              <span>Ready</span>
              <strong>{{ statistics.windows.gotowe }} items</strong>
            </div>
            <div class="progress-bar">
              <div class="progress-fill warning" :style="{ width: getPercentage(statistics.windows.gotowe, statistics.windows.total) + '%' }"></div>
            </div>
          </div>

          <div class="progress-item">
            <div class="progress-label">
              <span>Delivered</span>
              <strong>{{ statistics.windows.wydane }} items</strong>
            </div>
            <div class="progress-bar">
              <div class="progress-fill gray" :style="{ width: getPercentage(statistics.windows.wydane, statistics.windows.total) + '%' }"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Low Stock Alerts -->
      <div v-if="statistics.low_stock_alerts.length > 0" class="card alert-card">
        <div class="alert-header">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
            <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
          </svg>
          <h2>Low Stock Alerts</h2>
        </div>
        <div class="alerts-list">
          <div v-for="material in statistics.low_stock_alerts" :key="material.id" class="alert-item">
            <div class="alert-content">
              <strong>{{ material.name }}</strong>
              <p>Current: {{ material.current_stock }} {{ material.unit }} / Minimum: {{ material.min_stock }} {{ material.unit }}</p>
            </div>
            <RouterLink :to="`/materials`" class="btn btn-warning btn-small">Restock</RouterLink>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="quick-actions">
        <RouterLink to="/production-orders" class="action-card">
          <div class="action-icon primary">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
          </div>
          <h3>New Production Order</h3>
          <p>Create a new production order</p>
        </RouterLink>

        <RouterLink to="/materials" class="action-card">
          <div class="action-icon success">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
            </svg>
          </div>
          <h3>Warehouse</h3>
          <p>Manage inventory</p>
        </RouterLink>

        <RouterLink to="/windows" class="action-card">
          <div class="action-icon warning">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
            </svg>
          </div>
          <h3>Products</h3>
          <p>Window catalog</p>
        </RouterLink>

        <RouterLink to="/orders" class="action-card">
          <div class="action-icon secondary">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
            </svg>
          </div>
          <h3>Orders</h3>
          <p>View all orders</p>
        </RouterLink>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, computed } from 'vue'
import { RouterLink } from 'vue-router'
import { useDashboardStore } from '../stores'

const dashboardStore = useDashboardStore()

const statistics = computed(() => dashboardStore.statistics)
const loading = computed(() => dashboardStore.loading)

const getPercentage = (value, total) => {
  return total > 0 ? Math.round((value / total) * 100) : 0
}

const refreshData = () => {
  dashboardStore.fetchStatistics()
}

onMounted(() => {
  dashboardStore.fetchStatistics()
})
</script>

<style scoped>
.dashboard {
  animation: fadeIn 0.5s ease;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2.5rem;
  padding-bottom: 1.5rem;
  border-bottom: 2px solid var(--gray-200);
}

.dashboard-header h1 {
  font-size: 2.25rem;
  margin-bottom: 0.5rem;
  color: var(--gray-900);
}

.subtitle {
  color: var(--gray-600);
  font-size: 1rem;
  margin: 0;
}

.dashboard-header .btn {
  display: flex;
  gap: 0.5rem;
}

.dashboard-header .btn svg {
  width: 18px;
  height: 18px;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2.5rem;
}

.stat-card {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  box-shadow: var(--shadow);
  border: 1px solid var(--gray-200);
  transition: all var(--transition-base);
}

.stat-card:hover {
  box-shadow: var(--shadow-lg);
  transform: translateY(-4px);
}

.stat-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.stat-icon {
  width: 50px;
  height: 50px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.stat-icon svg {
  width: 26px;
  height: 26px;
  color: white;
}

.stat-icon.primary {
  background: var(--gradient-primary);
}

.stat-icon.success {
  background: var(--gradient-success);
}

.stat-icon.warning {
  background: linear-gradient(135deg, var(--warning) 0%, #F59E0B 100%);
}

.stat-icon.secondary {
  background: var(--gradient-secondary);
}

.stat-label {
  font-size: 0.95rem;
  color: var(--gray-600);
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.stat-value {
  font-size: 3rem;
  font-weight: 700;
  color: var(--gray-900);
  line-height: 1;
}

.card {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  box-shadow: var(--shadow);
  border: 1px solid var(--gray-200);
  margin-bottom: 2rem;
}

.card h2 {
  font-size: 1.5rem;
  margin-bottom: 1.5rem;
  color: var(--gray-900);
}

.progress-section {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.progress-item {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.progress-label {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.95rem;
}

.progress-label span {
  color: var(--gray-700);
  font-weight: 500;
}

.progress-label strong {
  color: var(--gray-900);
  font-weight: 600;
}

.progress-bar {
  height: 10px;
  background: var(--gray-200);
  border-radius: 5px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);
  animation: progressAnimation 1s ease;
  border-radius: 5px;
}

@keyframes progressAnimation {
  from { width: 0; }
}

.progress-fill.primary {
  background: var(--gradient-primary);
}

.progress-fill.success {
  background: var(--gradient-success);
}

.progress-fill.warning {
  background: linear-gradient(135deg, var(--warning) 0%, #F59E0B 100%);
}

.progress-fill.gray {
  background: linear-gradient(135deg, var(--gray-400) 0%, var(--gray-500) 100%);
}

.alert-card {
  background: linear-gradient(135deg, rgba(245, 158, 11, 0.05) 0%, rgba(245, 158, 11, 0.1) 100%);
  border-color: var(--warning);
}

.alert-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.alert-header svg {
  width: 28px;
  height: 28px;
  color: var(--warning);
}

.alert-header h2 {
  margin: 0;
  color: var(--warning);
}

.alerts-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.alert-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 1.25rem;
  background: white;
  border-radius: 12px;
  border: 1px solid var(--gray-200);
  transition: all var(--transition-base);
}

.alert-item:hover {
  box-shadow: var(--shadow);
  border-color: var(--warning);
}

.alert-content {
  flex: 1;
}

.alert-content strong {
  display: block;
  margin-bottom: 0.25rem;
  color: var(--gray-900);
  font-weight: 600;
}

.alert-content p {
  margin: 0;
  color: var(--gray-600);
  font-size: 0.9rem;
}

.btn-small {
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
}

.quick-actions {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 1.5rem;
}

.action-card {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  text-decoration: none;
  color: inherit;
  border: 1px solid var(--gray-200);
  box-shadow: var(--shadow);
  transition: all var(--transition-base);
  text-align: center;
}

.action-card:hover {
  transform: translateY(-6px);
  box-shadow: var(--shadow-xl);
}

.action-icon {
  width: 60px;
  height: 60px;
  margin: 0 auto 1.5rem;
  border-radius: 15px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.action-icon svg {
  width: 30px;
  height: 30px;
  color: white;
}

.action-icon.primary {
  background: var(--gradient-primary);
  box-shadow: var(--shadow-glow);
}

.action-icon.success {
  background: var(--gradient-success);
}

.action-icon.warning {
  background: linear-gradient(135deg, var(--warning) 0%, #F59E0B 100%);
}

.action-icon.secondary {
  background: var(--gradient-secondary);
}

.action-card h3 {
  font-size: 1.1rem;
  margin-bottom: 0.5rem;
  color: var(--gray-900);
}

.action-card p {
  margin: 0;
  color: var(--gray-600);
  font-size: 0.9rem;
}
</style>

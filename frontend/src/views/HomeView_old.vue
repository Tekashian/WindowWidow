<template>
  <div class="dashboard">
    <div class="dashboard-header">
      <div>
        <h1>🏭 Dashboard Produkcyjny</h1>
        <p>Witaj w systemie zarządzania produkcją okien</p>
      </div>
      <button @click="refreshData" class="btn btn-primary">
        🔄 Odśwież dane
      </button>
    </div>

    <div v-if="loading" class="loading">
      <div class="spinner"></div>
    </div>

    <div v-else-if="statistics" class="dashboard-content">
      <!-- Stats Cards -->
      <div class="stats-grid">
        <div class="stat-card card" style="--gradient: var(--gradient-1)">
          <div class="stat-icon">📋</div>
          <div class="stat-info">
            <h3>{{ statistics.production_orders.total }}</h3>
            <p>Wszystkie zlecenia</p>
          </div>
        </div>

        <div class="stat-card card" style="--gradient: var(--gradient-3)">
          <div class="stat-icon">🔄</div>
          <div class="stat-info">
            <h3>{{ statistics.production_orders.w_trakcie }}</h3>
            <p>W produkcji</p>
          </div>
        </div>

        <div class="stat-card card" style="--gradient: var(--gradient-4)">
          <div class="stat-icon">✅</div>
          <div class="stat-info">
            <h3>{{ statistics.production_orders.zakonczone }}</h3>
            <p>Dziś zakończone</p>
          </div>
        </div>

        <div class="stat-card card" style="--gradient: var(--gradient-2)">
          <div class="stat-icon">🪟</div>
          <div class="stat-info">
            <h3>{{ statistics.windows.total }}</h3>
            <p>Produkty</p>
          </div>
        </div>
      </div>

      <!-- Windows Status -->
      <div class="card">
        <h2>📊 Status Okien</h2>
        <div class="progress-section">
          <div class="progress-item">
            <div class="progress-label">
              <span>Projekty</span>
              <strong>{{ statistics.windows.projekt }}</strong>
            </div>
            <div class="progress-bar">
              <div class="progress-fill" :style="{ width: getPercentage(statistics.windows.projekt, statistics.windows.total) + '%', background: 'var(--gradient-1)' }"></div>
            </div>
          </div>

          <div class="progress-item">
            <div class="progress-label">
              <span>W produkcji</span>
              <strong>{{ statistics.windows.w_produkcji }}</strong>
            </div>
            <div class="progress-bar">
              <div class="progress-fill" :style="{ width: getPercentage(statistics.windows.w_produkcji, statistics.windows.total) + '%', background: 'var(--gradient-3)' }"></div>
            </div>
          </div>

          <div class="progress-item">
            <div class="progress-label">
              <span>Gotowe</span>
              <strong>{{ statistics.windows.gotowe }}</strong>
            </div>
            <div class="progress-bar">
              <div class="progress-fill" :style="{ width: getPercentage(statistics.windows.gotowe, statistics.windows.total) + '%', background: 'var(--gradient-4)' }"></div>
            </div>
          </div>

          <div class="progress-item">
            <div class="progress-label">
              <span>Wydane</span>
              <strong>{{ statistics.windows.wydane }}</strong>
            </div>
            <div class="progress-bar">
              <div class="progress-fill" :style="{ width: getPercentage(statistics.windows.wydane, statistics.windows.total) + '%', background: '#6b7280' }"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Low Stock Alerts -->
      <div v-if="statistics.low_stock_alerts.length > 0" class="card alert-card">
        <h2>⚠️ Niski Stan Magazynowy</h2>
        <div class="alerts-list">
          <div v-for="material in statistics.low_stock_alerts" :key="material.id" class="alert-item">
            <div class="alert-icon">📦</div>
            <div class="alert-content">
              <strong>{{ material.name }}</strong>
              <p>Stan: {{ material.current_stock }} {{ material.unit }} (Min: {{ material.min_stock }})</p>
            </div>
            <RouterLink :to="`/materials`" class="btn btn-warning">Uzupełnij</RouterLink>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="quick-actions">
        <RouterLink to="/production-orders/new" class="action-card card">
          <div class="action-icon">➕</div>
          <h3>Nowe Zlecenie</h3>
          <p>Utwórz zlecenie produkcyjne</p>
        </RouterLink>

        <RouterLink to="/materials" class="action-card card">
          <div class="action-icon">📦</div>
          <h3>Magazyn</h3>
          <p>Zarządzaj stanami</p>
        </RouterLink>

        <RouterLink to="/windows" class="action-card card">
          <div class="action-icon">🪟</div>
          <h3>Produkty</h3>
          <p>Katalog okien</p>
        </RouterLink>

        <RouterLink to="/orders" class="action-card card">
          <div class="action-icon">📋</div>
          <h3>Zamówienia</h3>
          <p>Lista zamówień</p>
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
.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.dashboard-header h1 {
  background: var(--gradient-1);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.5rem;
  background: white;
  position: relative;
  overflow: hidden;
}

.stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 4px;
  height: 100%;
  background: var(--gradient);
}

.stat-icon {
  font-size: 3rem;
  animation: bounce 2s infinite;
}

@keyframes bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}

.stat-info h3 {
  font-size: 2.5rem;
  margin: 0;
  background: var(--gradient);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.stat-info p {
  margin: 0;
  color: #6b7280;
}

.progress-section {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  margin-top: 1rem;
}

.progress-item {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.progress-label {
  display: flex;
  justify-content: space-between;
  font-size: 0.875rem;
}

.progress-bar {
  height: 12px;
  background: var(--border);
  border-radius: 6px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  transition: width 1s ease;
  animation: progressAnimation 1s ease;
}

@keyframes progressAnimation {
  from { width: 0; }
}

.alert-card {
  background: linear-gradient(135deg, #fef3c7 0%, #fde047 100%);
  border-left: 4px solid var(--warning);
}

.alerts-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-top: 1rem;
}

.alert-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: white;
  border-radius: 12px;
}

.alert-icon {
  font-size: 2rem;
}

.alert-content {
  flex: 1;
}

.alert-content strong {
  display: block;
  margin-bottom: 0.25rem;
}

.alert-content p {
  margin: 0;
  color: #6b7280;
  font-size: 0.875rem;
}

.quick-actions {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
  margin-top: 2rem;
}

.action-card {
  text-align: center;
  text-decoration: none;
  color: inherit;
  transition: all 0.3s ease;
}

.action-card:hover {
  transform: translateY(-8px) scale(1.05);
}

.action-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.action-card h3 {
  margin-bottom: 0.5rem;
  color: var(--dark);
}

.action-card p {
  margin: 0;
  color: #6b7280;
  font-size: 0.875rem;
}
</style>

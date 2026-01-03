<template>
  <div class="warehouse-dashboard">
    <div class="dashboard-header">
      <div class="header-content">
        <h1>Panel Magazynu</h1>
        <p>Zarządzanie dostawami i magazynem</p>
        <p class="company-name">WindowWidow</p>
      </div>
      <button @click="refresh" class="refresh-button" :disabled="loading">
        <span v-if="loading">⟳</span>
        <span v-else>↻</span>
        Odśwież
      </button>
    </div>

    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-label">Wszystkie</div>
        <div class="stat-value">{{ statistics.total_deliveries || 0 }}</div>
      </div>

      <div class="stat-card">
        <div class="stat-label">Oczekujące</div>
        <div class="stat-value">{{ statistics.pending || 0 }}</div>
      </div>

      <div class="stat-card">
        <div class="stat-label">W transporcie</div>
        <div class="stat-value">{{ statistics.in_transit || 0 }}</div>
      </div>

      <div class="stat-card">
        <div class="stat-label">Dostarczone</div>
        <div class="stat-value">{{ statistics.delivered || 0 }}</div>
      </div>

      <div class="stat-card">
        <div class="stat-label">Odrzucone</div>
        <div class="stat-value">{{ statistics.rejected || 0 }}</div>
      </div>
    </div>

    <div v-if="error" class="error-message">
      {{ error }}
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useWarehouseStore } from '../../stores/warehouseStore'

const warehouseStore = useWarehouseStore()

const loading = ref(false)
const error = ref(null)

const statistics = ref({
  total_deliveries: 0,
  pending: 0,
  in_transit: 0,
  delivered: 0,
  rejected: 0
})

const loadData = async () => {
  loading.value = true
  error.value = null

  try {
    if (warehouseStore.fetchStatistics) {
      const stats = await warehouseStore.fetchStatistics()
      if (stats) {
        statistics.value = stats
      }
    }
  } catch (err) {
    error.value = 'Nie udało się załadować danych'
    console.error('Failed to load warehouse data:', err)
  } finally {
    loading.value = false
  }
}

const refresh = () => {
  loadData()
}

onMounted(() => {
  loadData()
})
</script>

<style scoped>
.warehouse-dashboard {
  padding: 2rem;
  min-height: 100vh;
  background: linear-gradient(135deg, var(--dark) 0%, var(--darker) 100%);
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 2px solid rgba(0, 245, 255, 0.2);
}

.header-content h1 {
  color: var(--primary);
  font-size: 2.5rem;
  margin: 0 0 0.5rem 0;
  background: linear-gradient(135deg, var(--primary), #00d4ff);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.header-content p {
  color: rgba(255, 255, 255, 0.7);
  margin: 0.25rem 0;
  font-size: 1rem;
}

.company-name {
  font-weight: 600;
  color: var(--primary);
}

.refresh-button {
  background: var(--darker);
  border: 1px solid rgba(0, 245, 255, 0.3);
  color: var(--primary);
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  cursor: pointer;
  font-size: 1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.3s;
}

.refresh-button:hover:not(:disabled) {
  background: rgba(0, 245, 255, 0.1);
  border-color: var(--primary);
  transform: translateY(-2px);
}

.refresh-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.refresh-button span:first-child {
  display: inline-block;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: var(--darker);
  border: 1px solid rgba(0, 245, 255, 0.2);
  border-radius: 12px;
  padding: 1.5rem;
  backdrop-filter: blur(10px);
  transition: all 0.3s;
}

.stat-card:hover {
  transform: translateY(-4px);
  border-color: var(--primary);
  box-shadow: 0 8px 32px rgba(0, 245, 255, 0.2);
}

.stat-label {
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.9rem;
  margin-bottom: 0.5rem;
}

.stat-value {
  color: var(--primary);
  font-size: 2.5rem;
  font-weight: 700;
}

.error-message {
  background: rgba(255, 68, 68, 0.1);
  border: 1px solid #ff4444;
  color: #ff6b6b;
  padding: 1rem;
  border-radius: 8px;
  margin-top: 1rem;
}

@media (max-width: 768px) {
  .warehouse-dashboard {
    padding: 1rem;
  }

  .dashboard-header {
    flex-direction: column;
    gap: 1rem;
  }

  .refresh-button {
    width: 100%;
    justify-content: center;
  }

  .stats-grid {
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
  }

  .header-content h1 {
    font-size: 2rem;
  }
}
</style>

<template>
  <div class="issues-container">
    <!-- Header -->
    <div class="page-header">
      <h1 class="page-title">⚠️ Problemy Produkcyjne</h1>
      <p class="page-subtitle">Monitoruj i rozwiązuj problemy</p>
    </div>

    <!-- Statistics -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-label">Wszystkie</div>
        <div class="stat-value">{{ issues.length }}</div>
      </div>
      <div class="stat-card stat-critical">
        <div class="stat-label">Krytyczne</div>
        <div class="stat-value">{{ criticalCount }}</div>
      </div>
      <div class="stat-card stat-high">
        <div class="stat-label">Wysokie</div>
        <div class="stat-value">{{ highCount }}</div>
      </div>
      <div class="stat-card stat-open">
        <div class="stat-label">Otwarte</div>
        <div class="stat-value">{{ openCount }}</div>
      </div>
    </div>

    <!-- Filters -->
    <div class="filters-card">
      <div class="filters-grid">
        <div class="filter-group">
          <label class="filter-label">Priorytet</label>
          <select v-model="filters.severity" @change="applyFilters" class="filter-select">
            <option value="">Wszystkie</option>
            <option value="low">Niski</option>
            <option value="medium">Średni</option>
            <option value="high">Wysoki</option>
            <option value="critical">Krytyczny</option>
          </select>
        </div>

        <div class="filter-group">
          <label class="filter-label">Status</label>
          <select v-model="filters.status" @change="applyFilters" class="filter-select">
            <option value="">Wszystkie</option>
            <option value="open">Otwarte</option>
            <option value="in_progress">W trakcie</option>
            <option value="resolved">Rozwiązane</option>
            <option value="cancelled">Anulowane</option>
          </select>
        </div>

        <div class="filter-checkboxes">
          <label class="checkbox-label">
            <input type="checkbox" v-model="filters.critical_only" @change="applyFilters" class="filter-checkbox" />
            Tylko krytyczne
          </label>
        </div>
      </div>
    </div>

    <!-- Issues List -->
    <div class="issues-list">
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p class="loading-text">Ładowanie...</p>
      </div>

      <div v-else-if="error" class="error-state">
        <div class="error-message">⚠️ {{ error }}</div>
      </div>

      <div v-else-if="filteredIssues.length === 0" class="empty-state">
        Brak problemów
      </div>

      <div v-for="issue in filteredIssues" :key="issue.id" :class="['issue-card', issue.severity === 'critical' ? 'issue-critical' : '']" @click="viewIssueDetails(issue)">
        <div class="issue-content">
          <!-- Left: Issue Info -->
          <div class="issue-main">
            <div class="issue-badges">
              <span :class="['severity-badge', getSeverityClass(issue.severity)]">
                {{ getSeverityText(issue.severity) }}
              </span>
              <span class="type-badge">{{ getTypeText(issue.issue_type) }}</span>
              <span :class="['status-badge', issue.status === 'resolved' ? 'status-resolved' : 'status-pending']">
                {{ getStatusText(issue.status) }}
              </span>
            </div>

            <h3 class="issue-title">{{ issue.description }}</h3>

            <div class="issue-details">
              <div class="detail-item">
                Wpływ: <span :class="getImpactColor(issue.impact)">{{ getImpactText(issue.impact) }}</span>
              </div>
              <div v-if="issue.estimated_delay_hours" class="detail-item">
                Szacowane opóźnienie: <span class="delay-value">{{ issue.estimated_delay_hours }}h</span>
              </div>
              <div v-if="issue.production_order" class="detail-item">
                Zlecenie: <span class="order-number">{{ issue.production_order.order_number }}</span>
              </div>
            </div>
          </div>

          <!-- Right: Actions & Info -->
          <div class="issue-actions">
            <div class="issue-meta">
              <div v-if="issue.reporter">👤 {{ issue.reporter.name }}</div>
              <div>📅 {{ formatDate(issue.created_at) }}</div>
            </div>

            <button v-if="issue.status !== 'resolved'" @click.stop="resolveIssue(issue.id)" class="resolve-btn">
              ✅ Rozwiąż
            </button>
            <span v-else class="resolved-label">
              ✅ Rozwiązane {{ formatDate(issue.resolved_at) }}
            </span>
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

const router = useRouter();
const productionStore = useProductionStore();

const filters = ref({
  severity: '',
  status: '',
  critical_only: false
});

const issues = computed(() => productionStore.issues);
const loading = computed(() => productionStore.loading);
const error = computed(() => productionStore.error);

const filteredIssues = computed(() => {
  let result = [...issues.value];

  if (filters.value.severity) {
    result = result.filter(i => i.severity === filters.value.severity);
  }

  if (filters.value.status) {
    result = result.filter(i => i.status === filters.value.status);
  }

  if (filters.value.critical_only) {
    result = result.filter(i => i.severity === 'critical');
  }

  return result;
});

const criticalCount = computed(() => issues.value.filter(i => i.severity === 'critical' && i.status !== 'resolved').length);
const highCount = computed(() => issues.value.filter(i => i.severity === 'high' && i.status !== 'resolved').length);
const openCount = computed(() => issues.value.filter(i => i.status === 'open' || i.status === 'in_progress').length);

const getSeverityClass = (severity) => {
  const classes = {
    low: 'severity-low',
    medium: 'severity-medium',
    high: 'severity-high',
    critical: 'severity-critical'
  };
  return classes[severity] || classes.medium;
};

const getSeverityText = (severity) => {
  const texts = {
    low: 'Niski',
    medium: 'Średni',
    high: 'Wysoki',
    critical: '🚨 Krytyczny'
  };
  return texts[severity] || severity;
};

const getTypeText = (type) => {
  const texts = {
    material_shortage: '📦 Brak materiałów',
    equipment_failure: '⚙️ Awaria sprzętu',
    quality_defect: '🔍 Wada jakościowa',
    other: '❓ Inne'
  };
  return texts[type] || type;
};

const getStatusText = (status) => {
  const texts = {
    open: 'Otwarte',
    in_progress: 'W trakcie',
    resolved: 'Rozwiązane',
    cancelled: 'Anulowane'
  };
  return texts[status] || status;
};

const getImpactText = (impact) => {
  const texts = {
    none: 'Brak',
    minimal: 'Minimalny',
    moderate: 'Umiarkowany',
    severe: 'Poważny'
  };
  return texts[impact] || impact;
};

const getImpactColor = (impact) => {
  const colors = {
    none: 'impact-none',
    minimal: 'impact-minimal',
    moderate: 'impact-moderate',
    severe: 'impact-severe'
  };
  return colors[impact] || colors.moderate;
};

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

const applyFilters = () => {
  // Filters are computed reactively
};

const viewIssueDetails = (issue) => {
  if (issue.production_order_id) {
    router.push(`/production/orders/${issue.production_order_id}`);
  }
};

const resolveIssue = async (id) => {
  try {
    await productionStore.resolveIssue(id);
  } catch (err) {
    console.error('Failed to resolve issue:', err);
  }
};

onMounted(() => {
  productionStore.fetchIssues();
});
</script>

<style scoped>
.issues-container {
  min-height: 100vh;
  background: linear-gradient(135deg, var(--darker), var(--dark));
  padding: 1rem;
}

@media (min-width: 768px) {
  .issues-container {
    padding: 1.5rem;
  }
}

.page-header {
  margin-bottom: 1.5rem;
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

.stat-card {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  border: 1px solid var(--border);
  border-radius: 0.75rem;
  padding: 1rem;
  transition: all var(--transition-base);
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

.stat-critical {
  background: rgba(239, 68, 68, 0.1);
  border-color: rgba(239, 68, 68, 0.3);
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

.stat-critical .stat-label {
  color: #FCA5A5;
}

.stat-critical .stat-value {
  color: #F87171;
}

.stat-high {
  background: rgba(251, 146, 60, 0.1);
  border-color: rgba(251, 146, 60, 0.3);
}

.stat-high .stat-label {
  color: #FCD34D;
}

.stat-high .stat-value {
  color: #FBBF24;
}

.stat-open {
  background: rgba(16, 185, 129, 0.1);
  border-color: rgba(16, 185, 129, 0.3);
}

.stat-open .stat-label {
  color: #6EE7B7;
}

.stat-open .stat-value {
  color: #34D399;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.7;
  }
}

/* Filters */
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
}

@media (min-width: 768px) {
  .filters-grid {
    grid-template-columns: repeat(3, 1fr);
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
  transition: all var(--transition-base);
}

.filter-select:focus {
  outline: none;
  border-color: var(--primary);
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

/* Issues List */
.issues-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

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
  margin-bottom: 0.5rem;
}

.empty-state {
  color: var(--gray-400);
}

/* Issue Card */
.issue-card {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  border: 1px solid var(--border);
  border-radius: 0.75rem;
  padding: 1.5rem;
  cursor: pointer;
  transition: all var(--transition-base);
}

.issue-card:hover {
  border-color: var(--primary);
}

.issue-critical {
  border-color: rgba(239, 68, 68, 0.5);
  animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

.issue-content {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

@media (min-width: 768px) {
  .issue-content {
    flex-direction: row;
    justify-content: space-between;
  }
}

.issue-main {
  flex: 1;
}

.issue-badges {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 0.75rem;
  flex-wrap: wrap;
}

.severity-badge,
.type-badge,
.status-badge {
  padding: 0.375rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
}

.severity-low {
  background: rgba(107, 114, 128, 0.3);
  color: #D1D5DB;
}

.severity-medium {
  background: rgba(234, 179, 8, 0.3);
  color: #FDE68A;
}

.severity-high {
  background: rgba(251, 146, 60, 0.3);
  color: #FDBA74;
}

.severity-critical {
  background: rgba(239, 68, 68, 0.3);
  color: #FCA5A5;
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

.type-badge {
  color: var(--gray-400);
  font-size: 0.875rem;
  font-weight: 400;
  padding: 0;
}

.status-resolved {
  background: rgba(16, 185, 129, 0.2);
  color: #6EE7B7;
}

.status-pending {
  background: rgba(234, 179, 8, 0.2);
  color: #FDE68A;
}

.issue-title {
  color: white;
  font-weight: 500;
  margin-bottom: 0.5rem;
  font-size: 1rem;
}

.issue-details {
  font-size: 0.875rem;
  color: var(--gray-400);
}

.detail-item {
  margin-bottom: 0.25rem;
}

.impact-none {
  color: var(--gray-400);
}

.impact-minimal {
  color: #FDE68A;
}

.impact-moderate {
  color: #FBBF24;
}

.impact-severe {
  color: #F87171;
}

.delay-value {
  color: #FBBF24;
}

.order-number {
  color: var(--primary);
  font-family: 'Courier New', monospace;
}

/* Issue Actions */
.issue-actions {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.5rem;
}

.issue-meta {
  font-size: 0.875rem;
  color: var(--gray-400);
  text-align: right;
}

.issue-meta > div {
  margin-bottom: 0.25rem;
}

.resolve-btn {
  padding: 0.5rem 1rem;
  background: linear-gradient(135deg, #10B981, #059669);
  color: white;
  border: none;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: all var(--transition-base);
}

.resolve-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 0 15px rgba(16, 185, 129, 0.4);
}

.resolved-label {
  color: #34D399;
  font-size: 0.875rem;
}
</style>

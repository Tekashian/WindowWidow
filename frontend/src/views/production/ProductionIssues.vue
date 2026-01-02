<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 p-4 md:p-6">
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-purple-500">
        ⚠️ Problemy Produkcyjne
      </h1>
      <p class="text-gray-400 mt-2">Monitoruj i rozwiązuj problemy</p>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-gray-800/50 backdrop-blur border border-gray-700 rounded-xl p-4">
        <div class="text-gray-400 text-sm mb-1">Wszystkie</div>
        <div class="text-2xl font-bold text-white">{{ issues.length }}</div>
      </div>
      <div class="bg-red-900/20 backdrop-blur border border-red-700 rounded-xl p-4 animate-pulse">
        <div class="text-red-300 text-sm mb-1">Krytyczne</div>
        <div class="text-2xl font-bold text-red-400">{{ criticalCount }}</div>
      </div>
      <div class="bg-orange-900/20 backdrop-blur border border-orange-700 rounded-xl p-4">
        <div class="text-orange-300 text-sm mb-1">Wysokie</div>
        <div class="text-2xl font-bold text-orange-400">{{ highCount }}</div>
      </div>
      <div class="bg-green-900/20 backdrop-blur border border-green-700 rounded-xl p-4">
        <div class="text-green-300 text-sm mb-1">Otwarte</div>
        <div class="text-2xl font-bold text-green-400">{{ openCount }}</div>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-gray-800/50 backdrop-blur border border-gray-700 rounded-xl p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-gray-400 text-sm mb-2">Priorytet</label>
          <select
            v-model="filters.severity"
            @change="applyFilters"
            class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2"
          >
            <option value="">Wszystkie</option>
            <option value="low">Niski</option>
            <option value="medium">Średni</option>
            <option value="high">Wysoki</option>
            <option value="critical">Krytyczny</option>
          </select>
        </div>

        <div>
          <label class="block text-gray-400 text-sm mb-2">Status</label>
          <select
            v-model="filters.status"
            @change="applyFilters"
            class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2"
          >
            <option value="">Wszystkie</option>
            <option value="open">Otwarte</option>
            <option value="in_progress">W trakcie</option>
            <option value="resolved">Rozwiązane</option>
            <option value="cancelled">Anulowane</option>
          </select>
        </div>

        <div class="flex items-end gap-2">
          <label class="flex items-center text-gray-400 hover:text-red-400 cursor-pointer">
            <input
              type="checkbox"
              v-model="filters.critical_only"
              @change="applyFilters"
              class="mr-2 rounded bg-gray-900 border-gray-700"
            />
            Tylko krytyczne
          </label>
        </div>
      </div>
    </div>

    <!-- Issues List -->
    <div class="space-y-4">
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-cyan-400"></div>
        <p class="text-gray-400 mt-2">Ładowanie...</p>
      </div>

      <div v-else-if="error" class="text-center py-12">
        <div class="text-red-400 mb-2">⚠️ {{ error }}</div>
      </div>

      <div v-else-if="filteredIssues.length === 0" class="text-center py-12 text-gray-400">
        Brak problemów
      </div>

      <div
        v-for="issue in filteredIssues"
        :key="issue.id"
        :class="[
          'bg-gray-800/50 backdrop-blur border rounded-xl p-6 hover:border-cyan-500 transition-all cursor-pointer',
          issue.severity === 'critical' ? 'border-red-700 animate-pulse-slow' : 'border-gray-700'
        ]"
        @click="viewIssueDetails(issue)"
      >
        <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
          <!-- Left: Issue Info -->
          <div class="flex-1">
            <div class="flex items-center gap-3 mb-3">
              <span
                :class="[
                  'px-3 py-1 rounded-full text-xs font-bold uppercase',
                  getSeverityClass(issue.severity)
                ]"
              >
                {{ getSeverityText(issue.severity) }}
              </span>
              <span class="text-gray-400 text-sm">{{ getTypeText(issue.issue_type) }}</span>
              <span
                :class="[
                  'px-2 py-1 rounded text-xs',
                  issue.status === 'resolved' ? 'bg-green-900/50 text-green-300' : 'bg-yellow-900/50 text-yellow-300'
                ]"
              >
                {{ getStatusText(issue.status) }}
              </span>
            </div>

            <h3 class="text-white font-medium mb-2">{{ issue.description }}</h3>

            <div class="text-sm text-gray-400 space-y-1">
              <div>
                Wpływ: <span :class="getImpactColor(issue.impact)">{{ getImpactText(issue.impact) }}</span>
              </div>
              <div v-if="issue.estimated_delay_hours">
                Szacowane opóźnienie: <span class="text-orange-400">{{ issue.estimated_delay_hours }}h</span>
              </div>
              <div v-if="issue.production_order">
                Zlecenie: 
                <span class="text-cyan-400 font-mono">{{ issue.production_order.order_number }}</span>
              </div>
            </div>
          </div>

          <!-- Right: Actions & Info -->
          <div class="flex flex-col items-end gap-2">
            <div class="text-sm text-gray-400 text-right">
              <div v-if="issue.reporter">👤 {{ issue.reporter.name }}</div>
              <div>📅 {{ formatDate(issue.created_at) }}</div>
            </div>

            <button
              v-if="issue.status !== 'resolved'"
              @click.stop="resolveIssue(issue.id)"
              class="bg-green-600 hover:bg-green-500 text-white text-sm px-4 py-2 rounded-lg transition-all"
            >
              ✅ Rozwiąż
            </button>
            <span v-else class="text-green-400 text-sm">
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
    low: 'bg-gray-700 text-gray-300',
    medium: 'bg-yellow-900 text-yellow-300',
    high: 'bg-orange-900 text-orange-300',
    critical: 'bg-red-900 text-red-300 animate-pulse'
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
    none: 'text-gray-400',
    minimal: 'text-yellow-400',
    moderate: 'text-orange-400',
    severe: 'text-red-400'
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

<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 p-4 md:p-6">
    <!-- Header -->
    <div class="mb-6">
      <button
        @click="$router.back()"
        class="text-gray-400 hover:text-cyan-400 mb-4 flex items-center gap-2"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Powrót
      </button>
      
      <div v-if="order" class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 class="text-3xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-purple-500">
            {{ order.order_number || `Zlecenie #${order.id}` }}
          </h1>
          <div class="flex flex-wrap items-center gap-3 mt-2">
            <StatusBadge :status="order.status" />
            <PriorityIndicator :priority="order.priority" />
            <span v-if="isDelayed" class="text-red-400 text-sm">
              🚨 Opóźnione
            </span>
          </div>
        </div>

        <div class="flex flex-wrap gap-2">
          <button
            v-if="order.status === 'pending'"
            @click="showStartProductionModal = true"
            class="bg-cyan-600 hover:bg-cyan-500 text-white px-4 py-2 rounded-lg transition-all"
          >
            ▶️ Rozpocznij produkcję
          </button>
          <button
            @click="showUpdateStatusModal = true"
            class="bg-purple-600 hover:bg-purple-500 text-white px-4 py-2 rounded-lg transition-all"
          >
            🔄 Zmień status
          </button>
          <button
            @click="showReportIssueModal = true"
            class="bg-red-600 hover:bg-red-500 text-white px-4 py-2 rounded-lg transition-all"
          >
            ⚠️ Zgłoś problem
          </button>
        </div>
      </div>
    </div>

    <div v-if="loading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-cyan-400"></div>
      <p class="text-gray-400 mt-2">Ładowanie...</p>
    </div>

    <div v-else-if="error" class="text-center py-12">
      <div class="text-red-400 mb-2">⚠️ {{ error }}</div>
    </div>

    <div v-else-if="order" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left Column: Order Info -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Basic Info -->
        <div class="bg-gray-800/50 backdrop-blur border border-gray-700 rounded-xl p-6">
          <h2 class="text-xl font-bold text-cyan-400 mb-4">📋 Informacje podstawowe</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <div class="text-gray-400 text-sm mb-1">Ilość</div>
              <div class="text-white text-lg font-bold">{{ order.quantity }}</div>
            </div>
            <div>
              <div class="text-gray-400 text-sm mb-1">Typ źródła</div>
              <div class="text-white">{{ order.source_type === 'customer_order' ? 'Zamówienie klienta' : 'Uzupełnienie magazynu' }}</div>
            </div>
            <div>
              <div class="text-gray-400 text-sm mb-1">Przypisany do</div>
              <div class="text-white">{{ order.assigned_user?.name || '-' }}</div>
            </div>
            <div>
              <div class="text-gray-400 text-sm mb-1">Utworzone przez</div>
              <div class="text-white">{{ order.creator?.name || '-' }}</div>
            </div>
            <div>
              <div class="text-gray-400 text-sm mb-1">Data utworzenia</div>
              <div class="text-white">{{ formatDate(order.created_at) }}</div>
            </div>
            <div>
              <div class="text-gray-400 text-sm mb-1">Szacowany termin</div>
              <div :class="isDelayed ? 'text-red-400' : 'text-white'">
                {{ order.estimated_completion_at ? formatDate(order.estimated_completion_at) : '-' }}
              </div>
            </div>
          </div>
        </div>

        <!-- Timeline -->
        <div class="bg-gray-800/50 backdrop-blur border border-gray-700 rounded-xl p-6">
          <h2 class="text-xl font-bold text-cyan-400 mb-4">📜 Historia</h2>
          <TimelineItem :timeline="order.timeline || []" />
        </div>

        <!-- Batches -->
        <div v-if="order.batches && order.batches.length > 0" class="bg-gray-800/50 backdrop-blur border border-gray-700 rounded-xl p-6">
          <h2 class="text-xl font-bold text-cyan-400 mb-4">📦 Partie produkcyjne</h2>
          <div class="space-y-3">
            <div
              v-for="batch in order.batches"
              :key="batch.id"
              class="bg-gray-900/50 border border-gray-700 rounded-lg p-4"
            >
              <div class="flex justify-between items-start">
                <div>
                  <div class="font-mono text-cyan-400 font-bold">{{ batch.batch_number }}</div>
                  <div class="text-sm text-gray-400 mt-1">
                    Ilość: <span class="text-white">{{ batch.quantity }}</span>
                  </div>
                  <StatusBadge :status="batch.status" class="mt-2" />
                </div>
                <div v-if="batch.quality_check_passed !== null" class="text-right">
                  <span v-if="batch.quality_check_passed" class="text-green-400">
                    ✅ Kontrola jakości przeszła
                  </span>
                  <span v-else class="text-red-400">
                    ❌ Kontrola jakości niepomyślna
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Materials -->
        <div v-if="order.materials && order.materials.length > 0" class="bg-gray-800/50 backdrop-blur border border-gray-700 rounded-xl p-6">
          <h2 class="text-xl font-bold text-cyan-400 mb-4">🧱 Materiały</h2>
          <div class="space-y-3">
            <div
              v-for="material in order.materials"
              :key="material.id"
              class="bg-gray-900/50 border border-gray-700 rounded-lg p-4"
            >
              <div class="flex justify-between items-start">
                <div>
                  <div class="text-white font-medium">{{ material.material?.name || 'Materiał' }}</div>
                  <div class="text-sm text-gray-400 mt-1">
                    Wymagane: <span class="text-cyan-400">{{ material.quantity_required }}</span>
                    | Użyte: <span class="text-purple-400">{{ material.quantity_used || 0 }}</span>
                  </div>
                </div>
                <div v-if="material.reserved_at" class="text-sm text-green-400">
                  ✅ Zarezerwowane
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Issues & Actions -->
      <div class="space-y-6">
        <!-- Issues -->
        <div class="bg-gray-800/50 backdrop-blur border border-gray-700 rounded-xl p-6">
          <h2 class="text-xl font-bold text-red-400 mb-4">⚠️ Problemy</h2>
          <div v-if="order.issues && order.issues.length > 0" class="space-y-3">
            <div
              v-for="issue in order.issues"
              :key="issue.id"
              :class="[
                'border rounded-lg p-3',
                issue.severity === 'critical' ? 'bg-red-900/20 border-red-700' : 'bg-orange-900/20 border-orange-700'
              ]"
            >
              <div class="flex justify-between items-start mb-2">
                <span class="text-xs font-bold uppercase" :class="issue.severity === 'critical' ? 'text-red-400' : 'text-orange-400'">
                  {{ issue.severity }}
                </span>
                <span class="text-xs text-gray-400">{{ issue.issue_type }}</span>
              </div>
              <div class="text-sm text-gray-300">{{ issue.description }}</div>
              <div v-if="issue.status !== 'resolved'" class="mt-2 text-xs text-yellow-400">
                Status: {{ issue.status }}
              </div>
            </div>
          </div>
          <div v-else class="text-center py-4 text-gray-400">
            Brak zgłoszonych problemów
          </div>
        </div>

        <!-- Deliveries -->
        <div v-if="order.deliveries && order.deliveries.length > 0" class="bg-gray-800/50 backdrop-blur border border-gray-700 rounded-xl p-6">
          <h2 class="text-xl font-bold text-teal-400 mb-4">🚚 Dostawy</h2>
          <div class="space-y-3">
            <div
              v-for="delivery in order.deliveries"
              :key="delivery.id"
              class="bg-gray-900/50 border border-gray-700 rounded-lg p-3"
            >
              <div class="font-mono text-teal-400 text-sm">{{ delivery.delivery_number }}</div>
              <StatusBadge :status="delivery.status" class="mt-2" />
              <div class="text-xs text-gray-400 mt-2">
                {{ formatDate(delivery.expected_delivery_date) }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modals (simplified - full implementation would need separate components) -->
    <!-- Update Status Modal -->
    <div v-if="showUpdateStatusModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div class="bg-gray-800 border border-gray-700 rounded-xl p-6 max-w-md w-full">
        <h3 class="text-xl font-bold text-cyan-400 mb-4">Zmień status</h3>
        <div class="space-y-4">
          <div>
            <label class="block text-gray-400 text-sm mb-2">Nowy status</label>
            <select v-model="updateStatusForm.status" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2">
              <option value="in_progress">W produkcji</option>
              <option value="quality_check">Kontrola jakości</option>
              <option value="completed">Ukończone</option>
              <option value="on_hold">Wstrzymane</option>
            </select>
          </div>
          <div>
            <label class="block text-gray-400 text-sm mb-2">Notatki</label>
            <textarea v-model="updateStatusForm.notes" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2" rows="3"></textarea>
          </div>
          <div class="flex gap-2">
            <button @click="updateStatus" class="flex-1 bg-cyan-600 hover:bg-cyan-500 text-white py-2 rounded-lg">
              Zapisz
            </button>
            <button @click="showUpdateStatusModal = false" class="flex-1 bg-gray-700 hover:bg-gray-600 text-white py-2 rounded-lg">
              Anuluj
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Report Issue Modal -->
    <div v-if="showReportIssueModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div class="bg-gray-800 border border-gray-700 rounded-xl p-6 max-w-md w-full">
        <h3 class="text-xl font-bold text-red-400 mb-4">Zgłoś problem</h3>
        <div class="space-y-4">
          <div>
            <label class="block text-gray-400 text-sm mb-2">Typ problemu</label>
            <select v-model="reportIssueForm.issue_type" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2">
              <option value="material_shortage">Brak materiałów</option>
              <option value="equipment_failure">Awaria sprzętu</option>
              <option value="quality_defect">Wada jakościowa</option>
              <option value="other">Inne</option>
            </select>
          </div>
          <div>
            <label class="block text-gray-400 text-sm mb-2">Priorytet</label>
            <select v-model="reportIssueForm.severity" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2">
              <option value="low">Niski</option>
              <option value="medium">Średni</option>
              <option value="high">Wysoki</option>
              <option value="critical">Krytyczny</option>
            </select>
          </div>
          <div>
            <label class="block text-gray-400 text-sm mb-2">Opis</label>
            <textarea v-model="reportIssueForm.description" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2" rows="3"></textarea>
          </div>
          <div>
            <label class="block text-gray-400 text-sm mb-2">Wpływ</label>
            <select v-model="reportIssueForm.impact" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2">
              <option value="none">Brak</option>
              <option value="minimal">Minimalny</option>
              <option value="moderate">Umiarkowany</option>
              <option value="severe">Poważny</option>
            </select>
          </div>
          <div class="flex gap-2">
            <button @click="reportIssue" class="flex-1 bg-red-600 hover:bg-red-500 text-white py-2 rounded-lg">
              Zgłoś
            </button>
            <button @click="showReportIssueModal = false" class="flex-1 bg-gray-700 hover:bg-gray-600 text-white py-2 rounded-lg">
              Anuluj
            </button>
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
  productionStore.fetchOrder(route.params.id);
});
</script>

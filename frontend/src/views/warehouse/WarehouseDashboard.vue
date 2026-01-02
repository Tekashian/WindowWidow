<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 p-4 md:p-6">
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-3xl md:text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-purple-500">
        📦 Panel Magazynu
      </h1>
      <p class="text-gray-400 mt-2">Zarządzanie dostawami z produkcji</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-yellow-900/20 backdrop-blur border border-yellow-700 rounded-xl p-4 hover:border-yellow-500 transition-all">
        <div class="text-yellow-300 text-sm mb-1">Oczekujące</div>
        <div class="text-2xl font-bold text-yellow-400">{{ statistics.pending }}</div>
      </div>

      <div class="bg-blue-900/20 backdrop-blur border border-blue-700 rounded-xl p-4 hover:border-blue-500 transition-all">
        <div class="text-blue-300 text-sm mb-1">W transporcie</div>
        <div class="text-2xl font-bold text-blue-400">{{ statistics.in_transit }}</div>
      </div>

      <div class="bg-orange-900/20 backdrop-blur border border-orange-700 rounded-xl p-4 hover:border-orange-500 transition-all animate-pulse">
        <div class="text-orange-300 text-sm mb-1">Opóźnione</div>
        <div class="text-2xl font-bold text-orange-400">{{ statistics.delayed }}</div>
      </div>

      <div class="bg-green-900/20 backdrop-blur border border-green-700 rounded-xl p-4 hover:border-green-500 transition-all">
        <div class="text-green-300 text-sm mb-1">Dzisiaj odebrane</div>
        <div class="text-2xl font-bold text-green-400">{{ statistics.delivered_today }}</div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
      <button
        @click="showFilter = 'pending'"
        class="bg-gradient-to-r from-yellow-600 to-yellow-500 hover:from-yellow-500 hover:to-yellow-400 text-white font-bold py-4 px-6 rounded-xl transition-all transform hover:scale-105 shadow-lg"
      >
        <svg class="w-6 h-6 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        Oczekujące dostawy
      </button>

      <button
        @click="showFilter = 'in_transit'"
        class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-400 text-white font-bold py-4 px-6 rounded-xl transition-all transform hover:scale-105 shadow-lg"
      >
        <svg class="w-6 h-6 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
        </svg>
        W transporcie
      </button>
    </div>

    <!-- Deliveries List -->
    <div class="bg-gray-800/50 backdrop-blur border border-gray-700 rounded-xl p-6">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-cyan-400">🚚 Dostawy</h2>
        <div class="flex gap-2">
          <button
            @click="showFilter = null; loadDeliveries()"
            :class="[
              'px-3 py-1 rounded-lg text-sm transition-all',
              !showFilter ? 'bg-cyan-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600'
            ]"
          >
            Wszystkie
          </button>
          <button
            @click="showFilter = 'pending'; loadDeliveries()"
            :class="[
              'px-3 py-1 rounded-lg text-sm transition-all',
              showFilter === 'pending' ? 'bg-yellow-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600'
            ]"
          >
            Oczekujące
          </button>
          <button
            @click="showFilter = 'delayed'; loadDeliveries()"
            :class="[
              'px-3 py-1 rounded-lg text-sm transition-all',
              showFilter === 'delayed' ? 'bg-orange-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600'
            ]"
          >
            Opóźnione
          </button>
        </div>
      </div>

      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-cyan-400"></div>
        <p class="text-gray-400 mt-2">Ładowanie dostaw...</p>
      </div>

      <div v-else-if="error" class="text-center py-12">
        <div class="text-red-400 mb-2">⚠️ {{ error }}</div>
        <button @click="loadDeliveries" class="text-cyan-400 hover:text-cyan-300 underline">
          Spróbuj ponownie
        </button>
      </div>

      <div v-else-if="filteredDeliveries.length === 0" class="text-center py-12 text-gray-400">
        Brak dostaw do wyświetlenia
      </div>

      <div v-else class="space-y-4">
        <div
          v-for="delivery in filteredDeliveries"
          :key="delivery.id"
          :class="[
            'bg-gray-900/50 border rounded-xl p-6 hover:border-cyan-500 cursor-pointer transition-all',
            isDelayed(delivery) ? 'border-orange-700 animate-pulse-slow' : 'border-gray-700'
          ]"
          @click="viewDelivery(delivery.id)"
        >
          <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
            <!-- Left: Delivery Info -->
            <div class="flex-1">
              <div class="flex items-center gap-3 mb-3">
                <span class="font-mono text-cyan-400 font-bold text-lg">
                  {{ delivery.delivery_number }}
                </span>
                <DeliveryStatusBadge :status="delivery.status" />
                <span v-if="isDelayed(delivery)" class="text-orange-400 text-sm">
                  ⏰ Opóźnione
                </span>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                <div>
                  <span class="text-gray-400">Zlecenie:</span>
                  <span class="text-white ml-2 font-mono">
                    {{ delivery.production_order?.order_number || '-' }}
                  </span>
                </div>
                <div>
                  <span class="text-gray-400">Partia:</span>
                  <span class="text-white ml-2 font-mono">
                    {{ delivery.batch?.batch_number || '-' }}
                  </span>
                </div>
                <div>
                  <span class="text-gray-400">Oczekiwana data:</span>
                  <span :class="isDelayed(delivery) ? 'text-orange-400 ml-2' : 'text-white ml-2'">
                    {{ formatDate(delivery.expected_delivery_date) }}
                  </span>
                </div>
                <div v-if="delivery.actual_delivery_date">
                  <span class="text-gray-400">Dostarczone:</span>
                  <span class="text-green-400 ml-2">
                    {{ formatDate(delivery.actual_delivery_date) }}
                  </span>
                </div>
              </div>

              <div v-if="delivery.notes" class="mt-3 text-sm text-gray-300 italic">
                💬 {{ delivery.notes }}
              </div>
            </div>

            <!-- Right: Actions -->
            <div class="flex flex-col gap-2">
              <button
                v-if="delivery.status === 'pending'"
                @click.stop="shipDelivery(delivery.id)"
                class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-lg text-sm transition-all whitespace-nowrap"
              >
                🚚 Wyślij
              </button>
              <button
                v-if="delivery.status === 'in_transit'"
                @click.stop="showReceiveModal(delivery)"
                class="bg-green-600 hover:bg-green-500 text-white px-4 py-2 rounded-lg text-sm transition-all whitespace-nowrap"
              >
                ✅ Odbierz
              </button>
              <button
                v-if="delivery.status === 'in_transit'"
                @click.stop="showRejectModal(delivery)"
                class="bg-red-600 hover:bg-red-500 text-white px-4 py-2 rounded-lg text-sm transition-all whitespace-nowrap"
              >
                ❌ Odrzuć
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Receive Modal -->
    <div v-if="receiveModalDelivery" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div class="bg-gray-800 border border-gray-700 rounded-xl p-6 max-w-md w-full">
        <h3 class="text-xl font-bold text-green-400 mb-4">✅ Potwierdź odbiór</h3>
        <p class="text-gray-300 mb-4">
          Czy na pewno chcesz potwierdzić odbiór dostawy 
          <span class="font-mono text-cyan-400">{{ receiveModalDelivery.delivery_number }}</span>?
        </p>
        <div class="mb-4">
          <label class="block text-gray-400 text-sm mb-2">Notatki (opcjonalne)</label>
          <textarea
            v-model="receiveNotes"
            class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2"
            rows="3"
            placeholder="Dodaj notatki o dostawie..."
          ></textarea>
        </div>
        <div class="flex gap-2">
          <button
            @click="confirmReceive"
            class="flex-1 bg-green-600 hover:bg-green-500 text-white py-2 rounded-lg transition-all"
          >
            Potwierdź
          </button>
          <button
            @click="receiveModalDelivery = null; receiveNotes = ''"
            class="flex-1 bg-gray-700 hover:bg-gray-600 text-white py-2 rounded-lg transition-all"
          >
            Anuluj
          </button>
        </div>
      </div>
    </div>

    <!-- Reject Modal -->
    <div v-if="rejectModalDelivery" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div class="bg-gray-800 border border-gray-700 rounded-xl p-6 max-w-md w-full">
        <h3 class="text-xl font-bold text-red-400 mb-4">❌ Odrzuć dostawę</h3>
        <p class="text-gray-300 mb-4">
          Odrzucasz dostawę 
          <span class="font-mono text-cyan-400">{{ rejectModalDelivery.delivery_number }}</span>
        </p>
        <div class="mb-4">
          <label class="block text-gray-400 text-sm mb-2">Powód odrzucenia *</label>
          <textarea
            v-model="rejectNotes"
            class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2"
            rows="3"
            placeholder="Opisz powód odrzucenia..."
            required
          ></textarea>
        </div>
        <div class="flex gap-2">
          <button
            @click="confirmReject"
            :disabled="!rejectNotes"
            class="flex-1 bg-red-600 hover:bg-red-500 text-white py-2 rounded-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Odrzuć
          </button>
          <button
            @click="rejectModalDelivery = null; rejectNotes = ''"
            class="flex-1 bg-gray-700 hover:bg-gray-600 text-white py-2 rounded-lg transition-all"
          >
            Anuluj
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useWarehouseStore } from '../../stores/warehouseStore';
import DeliveryStatusBadge from './components/DeliveryStatusBadge.vue';

const router = useRouter();
const warehouseStore = useWarehouseStore();

const showFilter = ref(null);
const receiveModalDelivery = ref(null);
const rejectModalDelivery = ref(null);
const receiveNotes = ref('');
const rejectNotes = ref('');

const deliveries = computed(() => warehouseStore.deliveries);
const statistics = computed(() => warehouseStore.statistics);
const loading = computed(() => warehouseStore.loading);
const error = computed(() => warehouseStore.error);

const filteredDeliveries = computed(() => {
  if (!showFilter.value) return deliveries.value;
  
  if (showFilter.value === 'pending') {
    return warehouseStore.pendingDeliveries;
  }
  if (showFilter.value === 'in_transit') {
    return warehouseStore.inTransitDeliveries;
  }
  if (showFilter.value === 'delayed') {
    return warehouseStore.delayedDeliveries;
  }
  
  return deliveries.value;
});

const isDelayed = (delivery) => {
  if (delivery.status !== 'pending') return false;
  const now = new Date();
  const expected = new Date(delivery.expected_delivery_date);
  return expected < now;
};

const formatDate = (dateString) => {
  if (!dateString) return '-';
  const date = new Date(dateString);
  return date.toLocaleDateString('pl-PL', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  });
};

const loadDeliveries = async () => {
  const filters = {};
  if (showFilter.value && showFilter.value !== 'delayed') {
    filters.status = showFilter.value;
  }
  if (showFilter.value === 'delayed') {
    filters.delayed_only = true;
  }
  await warehouseStore.fetchDeliveries(filters);
};

const shipDelivery = async (id) => {
  try {
    await warehouseStore.shipDelivery(id);
  } catch (err) {
    console.error('Failed to ship delivery:', err);
  }
};

const showReceiveModal = (delivery) => {
  receiveModalDelivery.value = delivery;
  receiveNotes.value = '';
};

const showRejectModal = (delivery) => {
  rejectModalDelivery.value = delivery;
  rejectNotes.value = '';
};

const confirmReceive = async () => {
  try {
    await warehouseStore.receiveDelivery(receiveModalDelivery.value.id, receiveNotes.value || null);
    receiveModalDelivery.value = null;
    receiveNotes.value = '';
  } catch (err) {
    console.error('Failed to receive delivery:', err);
  }
};

const confirmReject = async () => {
  if (!rejectNotes.value) return;
  
  try {
    await warehouseStore.rejectDelivery(rejectModalDelivery.value.id, rejectNotes.value);
    rejectModalDelivery.value = null;
    rejectNotes.value = '';
  } catch (err) {
    console.error('Failed to reject delivery:', err);
  }
};

const viewDelivery = (id) => {
  router.push(`/warehouse/deliveries/${id}`);
};

const refreshData = async () => {
  await Promise.all([
    loadDeliveries(),
    warehouseStore.fetchStatistics()
  ]);
};

onMounted(() => {
  refreshData();
});
</script>

<style scoped>
@keyframes pulse-slow {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.7; }
}

.animate-pulse-slow {
  animation: pulse-slow 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>

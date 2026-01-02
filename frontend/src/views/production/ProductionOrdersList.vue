<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 p-4 md:p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-3xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-purple-500">
          📋 Lista Zleceń Produkcyjnych
        </h1>
        <p class="text-gray-400 mt-2">Zarządzaj wszystkimi zleceniami produkcyjnymi</p>
      </div>
      <button
        @click="$router.push('/production/orders/new')"
        class="bg-gradient-to-r from-cyan-600 to-cyan-500 hover:from-cyan-500 hover:to-cyan-400 text-white font-bold py-2 px-4 rounded-lg transition-all"
      >
        <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Nowe zlecenie
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-gray-800/50 backdrop-blur border border-gray-700 rounded-xl p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Status Filter -->
        <div>
          <label class="block text-gray-400 text-sm mb-2">Status</label>
          <select
            v-model="filters.status"
            @change="applyFilters"
            class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 focus:border-cyan-500 focus:outline-none"
          >
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
        <div>
          <label class="block text-gray-400 text-sm mb-2">Priorytet</label>
          <select
            v-model="filters.priority"
            @change="applyFilters"
            class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 focus:border-cyan-500 focus:outline-none"
          >
            <option value="">Wszystkie</option>
            <option value="low">Niski</option>
            <option value="normal">Normalny</option>
            <option value="high">Wysoki</option>
            <option value="urgent">Pilne</option>
          </select>
        </div>

        <!-- Show Filters -->
        <div class="flex items-end gap-2">
          <label class="flex items-center text-gray-400 hover:text-cyan-400 cursor-pointer">
            <input
              type="checkbox"
              v-model="filters.in_progress"
              @change="applyFilters"
              class="mr-2 rounded bg-gray-900 border-gray-700 text-cyan-500 focus:ring-cyan-500"
            />
            W produkcji
          </label>
        </div>

        <div class="flex items-end gap-2">
          <label class="flex items-center text-gray-400 hover:text-red-400 cursor-pointer">
            <input
              type="checkbox"
              v-model="filters.delayed"
              @change="applyFilters"
              class="mr-2 rounded bg-gray-900 border-gray-700 text-red-500 focus:ring-red-500"
            />
            Opóźnione
          </label>
        </div>
      </div>

      <div class="flex justify-between items-center mt-4">
        <div class="text-sm text-gray-400">
          Znaleziono: <span class="text-cyan-400 font-bold">{{ orders.length }}</span> zleceń
        </div>
        <button
          @click="clearFilters"
          class="text-gray-400 hover:text-cyan-400 text-sm underline"
        >
          Wyczyść filtry
        </button>
      </div>
    </div>

    <!-- Orders Table -->
    <div class="bg-gray-800/50 backdrop-blur border border-gray-700 rounded-xl overflow-hidden">
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-cyan-400"></div>
        <p class="text-gray-400 mt-2">Ładowanie zleceń...</p>
      </div>

      <div v-else-if="error" class="text-center py-12">
        <div class="text-red-400 mb-2">⚠️ {{ error }}</div>
        <button
          @click="loadOrders"
          class="text-cyan-400 hover:text-cyan-300 underline"
        >
          Spróbuj ponownie
        </button>
      </div>

      <div v-else-if="orders.length === 0" class="text-center py-12 text-gray-400">
        Brak zleceń spełniających kryteria
      </div>

      <!-- Desktop View -->
      <div v-else class="hidden md:block overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-900/50">
            <tr class="text-left text-gray-400 text-sm">
              <th class="px-6 py-4">Numer zlecenia</th>
              <th class="px-6 py-4">Status</th>
              <th class="px-6 py-4">Priorytet</th>
              <th class="px-6 py-4">Ilość</th>
              <th class="px-6 py-4">Przypisany do</th>
              <th class="px-6 py-4">Data utworzenia</th>
              <th class="px-6 py-4">Termin</th>
              <th class="px-6 py-4">Akcje</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-700">
            <tr
              v-for="order in orders"
              :key="order.id"
              @click="viewOrder(order.id)"
              class="hover:bg-gray-900/50 cursor-pointer transition-colors"
            >
              <td class="px-6 py-4">
                <span class="font-mono text-cyan-400 font-bold">
                  {{ order.order_number || `#${order.id}` }}
                </span>
              </td>
              <td class="px-6 py-4">
                <StatusBadge :status="order.status" />
              </td>
              <td class="px-6 py-4">
                <PriorityIndicator :priority="order.priority" />
              </td>
              <td class="px-6 py-4 text-white">
                {{ order.quantity }}
              </td>
              <td class="px-6 py-4 text-gray-300">
                {{ order.assigned_user?.name || '-' }}
              </td>
              <td class="px-6 py-4 text-gray-400 text-sm">
                {{ formatDate(order.created_at) }}
              </td>
              <td class="px-6 py-4 text-sm">
                <span
                  v-if="order.estimated_completion_at"
                  :class="isDelayed(order) ? 'text-red-400' : 'text-gray-400'"
                >
                  {{ formatDate(order.estimated_completion_at) }}
                </span>
                <span v-else class="text-gray-500">-</span>
              </td>
              <td class="px-6 py-4">
                <button
                  @click.stop="viewOrder(order.id)"
                  class="text-cyan-400 hover:text-cyan-300 mr-3"
                  title="Zobacz szczegóły"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
      <div class="md:hidden divide-y divide-gray-700">
        <div
          v-for="order in orders"
          :key="order.id"
          @click="viewOrder(order.id)"
          class="p-4 hover:bg-gray-900/50 cursor-pointer transition-colors"
        >
          <div class="flex justify-between items-start mb-2">
            <span class="font-mono text-cyan-400 font-bold">
              {{ order.order_number || `#${order.id}` }}
            </span>
            <PriorityIndicator :priority="order.priority" />
          </div>
          <StatusBadge :status="order.status" class="mb-2" />
          <div class="text-sm text-gray-400 space-y-1">
            <div>Ilość: <span class="text-white">{{ order.quantity }}</span></div>
            <div v-if="order.assigned_user">
              Przypisany: <span class="text-white">{{ order.assigned_user.name }}</span>
            </div>
            <div>Data: {{ formatDate(order.created_at) }}</div>
            <div v-if="order.estimated_completion_at" :class="isDelayed(order) ? 'text-red-400' : ''">
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

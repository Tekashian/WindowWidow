<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 p-4 md:p-6">
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-3xl md:text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-purple-500">
        🏭 Panel Produkcji
      </h1>
      <p class="text-gray-400 mt-2">Zarządzanie zleceniami produkcyjnymi WindowWidow</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4 mb-6">
      <!-- Total Orders -->
      <div class="bg-gray-800/50 backdrop-blur border border-gray-700 rounded-xl p-4 hover:border-cyan-500 transition-all">
        <div class="text-gray-400 text-sm mb-1">Wszystkie</div>
        <div class="text-2xl font-bold text-white">{{ statistics.total_orders }}</div>
      </div>

      <!-- Pending -->
      <div class="bg-gray-800/50 backdrop-blur border border-gray-700 rounded-xl p-4 hover:border-blue-500 transition-all">
        <div class="text-gray-400 text-sm mb-1">Oczekujące</div>
        <div class="text-2xl font-bold text-blue-400">{{ statistics.pending }}</div>
      </div>

      <!-- In Progress -->
      <div class="bg-purple-900/20 backdrop-blur border border-purple-700 rounded-xl p-4 hover:border-purple-500 transition-all">
        <div class="text-purple-300 text-sm mb-1">W produkcji</div>
        <div class="text-2xl font-bold text-purple-400">{{ statistics.in_progress }}</div>
      </div>

      <!-- Completed -->
      <div class="bg-green-900/20 backdrop-blur border border-green-700 rounded-xl p-4 hover:border-green-500 transition-all">
        <div class="text-green-300 text-sm mb-1">Ukończone</div>
        <div class="text-2xl font-bold text-green-400">{{ statistics.completed }}</div>
      </div>

      <!-- Delayed -->
      <div class="bg-orange-900/20 backdrop-blur border border-orange-700 rounded-xl p-4 hover:border-orange-500 transition-all">
        <div class="text-orange-300 text-sm mb-1">Opóźnione</div>
        <div class="text-2xl font-bold text-orange-400">{{ statistics.delayed }}</div>
      </div>

      <!-- On Hold -->
      <div class="bg-yellow-900/20 backdrop-blur border border-yellow-700 rounded-xl p-4 hover:border-yellow-500 transition-all">
        <div class="text-yellow-300 text-sm mb-1">Wstrzymane</div>
        <div class="text-2xl font-bold text-yellow-400">{{ statistics.on_hold }}</div>
      </div>

      <!-- Critical Issues -->
      <div class="bg-red-900/20 backdrop-blur border border-red-700 rounded-xl p-4 hover:border-red-500 transition-all animate-pulse">
        <div class="text-red-300 text-sm mb-1">Krytyczne</div>
        <div class="text-2xl font-bold text-red-400">{{ statistics.critical_issues }}</div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
      <button
        @click="$router.push('/production/orders/new')"
        class="bg-gradient-to-r from-cyan-600 to-cyan-500 hover:from-cyan-500 hover:to-cyan-400 text-white font-bold py-4 px-6 rounded-xl transition-all transform hover:scale-105 shadow-lg shadow-cyan-500/20"
      >
        <svg class="w-6 h-6 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Nowe zlecenie
      </button>

      <button
        @click="$router.push('/production/orders')"
        class="bg-gradient-to-r from-purple-600 to-purple-500 hover:from-purple-500 hover:to-purple-400 text-white font-bold py-4 px-6 rounded-xl transition-all transform hover:scale-105 shadow-lg shadow-purple-500/20"
      >
        <svg class="w-6 h-6 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        Lista zleceń
      </button>

      <button
        @click="$router.push('/production/issues')"
        class="bg-gradient-to-r from-red-600 to-red-500 hover:from-red-500 hover:to-red-400 text-white font-bold py-4 px-6 rounded-xl transition-all transform hover:scale-105 shadow-lg shadow-red-500/20"
      >
        <svg class="w-6 h-6 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        Problemy ({{ statistics.critical_issues }})
      </button>
    </div>

    <!-- Recent Orders -->
    <div class="bg-gray-800/50 backdrop-blur border border-gray-700 rounded-xl p-6">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-cyan-400">📋 Ostatnie zlecenia</h2>
        <button
          @click="refreshData"
          :disabled="loading"
          class="text-gray-400 hover:text-cyan-400 transition-colors disabled:opacity-50"
        >
          <svg 
            :class="{ 'animate-spin': loading }"
            class="w-5 h-5" 
            fill="none" 
            stroke="currentColor" 
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
          </svg>
        </button>
      </div>

      <div v-if="loading" class="text-center py-8">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-cyan-400"></div>
        <p class="text-gray-400 mt-2">Ładowanie...</p>
      </div>

      <div v-else-if="error" class="text-center py-8">
        <div class="text-red-400 mb-2">⚠️ {{ error }}</div>
        <button
          @click="refreshData"
          class="text-cyan-400 hover:text-cyan-300 underline"
        >
          Spróbuj ponownie
        </button>
      </div>

      <div v-else-if="orders.length === 0" class="text-center py-8 text-gray-400">
        Brak zleceń do wyświetlenia
      </div>

      <div v-else class="space-y-3">
        <div
          v-for="order in recentOrders"
          :key="order.id"
          @click="$router.push(`/production/orders/${order.id}`)"
          class="bg-gray-900/50 border border-gray-700 rounded-lg p-4 hover:border-cyan-500 cursor-pointer transition-all"
        >
          <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-2">
                <span class="font-mono text-cyan-400 font-bold">{{ order.order_number || `#${order.id}` }}</span>
                <StatusBadge :status="order.status" />
                <PriorityIndicator :priority="order.priority" />
              </div>
              <div class="text-gray-400 text-sm">
                Ilość: <span class="text-white">{{ order.quantity }}</span>
                <span v-if="order.windows"> | {{ order.windows.name }}</span>
              </div>
            </div>

            <div class="text-sm text-gray-400">
              <div v-if="order.assigned_user" class="mb-1">
                👤 {{ order.assigned_user.name }}
              </div>
              <div>📅 {{ formatDate(order.created_at) }}</div>
            </div>
          </div>
        </div>
      </div>

      <div v-if="orders.length > 5" class="mt-4 text-center">
        <button
          @click="$router.push('/production/orders')"
          class="text-cyan-400 hover:text-cyan-300 underline"
        >
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

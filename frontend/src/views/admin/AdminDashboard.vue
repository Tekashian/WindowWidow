<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 p-4 md:p-6">
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-3xl md:text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-purple-500">
        ⚙️ Panel Administracyjny
      </h1>
      <p class="text-gray-400 mt-2">Zarządzanie systemem WindowWidow</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-cyan-900/20 backdrop-blur border border-cyan-700 rounded-xl p-4 hover:border-cyan-500 transition-all">
        <div class="text-cyan-300 text-sm mb-1">Typy okien</div>
        <div class="text-2xl font-bold text-cyan-400">{{ windowsCount }}</div>
      </div>

      <div class="bg-purple-900/20 backdrop-blur border border-purple-700 rounded-xl p-4 hover:border-purple-500 transition-all">
        <div class="text-purple-300 text-sm mb-1">Aktywne</div>
        <div class="text-2xl font-bold text-purple-400">{{ activeWindowsCount }}</div>
      </div>

      <div class="bg-green-900/20 backdrop-blur border border-green-700 rounded-xl p-4 hover:border-green-500 transition-all">
        <div class="text-green-300 text-sm mb-1">Wyróżnione</div>
        <div class="text-2xl font-bold text-green-400">{{ featuredCount }}</div>
      </div>

      <div class="bg-blue-900/20 backdrop-blur border border-blue-700 rounded-xl p-4 hover:border-blue-500 transition-all">
        <div class="text-blue-300 text-sm mb-1">Materiały</div>
        <div class="text-2xl font-bold text-blue-400">{{ materialsCount }}</div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
      <button
        @click="$router.push('/admin/windows/new')"
        class="bg-gradient-to-r from-cyan-600 to-cyan-500 hover:from-cyan-500 hover:to-cyan-400 text-white font-bold py-4 px-6 rounded-xl transition-all transform hover:scale-105 shadow-lg"
      >
        <svg class="w-6 h-6 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Dodaj typ okna
      </button>

      <button
        @click="$router.push('/admin/windows')"
        class="bg-gradient-to-r from-purple-600 to-purple-500 hover:from-purple-500 hover:to-purple-400 text-white font-bold py-4 px-6 rounded-xl transition-all transform hover:scale-105 shadow-lg"
      >
        <svg class="w-6 h-6 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
        </svg>
        Zarządzaj oknami
      </button>

      <button
        @click="$router.push('/materials')"
        class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-400 text-white font-bold py-4 px-6 rounded-xl transition-all transform hover:scale-105 shadow-lg"
      >
        <svg class="w-6 h-6 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
        </svg>
        Zarządzaj materiałami
      </button>
    </div>

    <!-- Recent Windows -->
    <div class="bg-gray-800/50 backdrop-blur border border-gray-700 rounded-xl p-6">
      <h2 class="text-xl font-bold text-cyan-400 mb-4">🪟 Ostatnio dodane okna</h2>
      
      <div v-if="recentWindows.length === 0" class="text-center py-8 text-gray-400">
        Brak okien w systemie. Dodaj pierwsze okno!
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div
          v-for="window in recentWindows"
          :key="window.id"
          @click="$router.push(`/admin/windows/${window.id}`)"
          class="bg-gray-900/50 border border-gray-700 rounded-lg p-4 hover:border-cyan-500 cursor-pointer transition-all group"
        >
          <div class="aspect-video bg-gray-800 rounded-lg mb-3 overflow-hidden">
            <img
              v-if="window.image_url"
              :src="window.image_url"
              :alt="window.name"
              class="w-full h-full object-cover group-hover:scale-110 transition-transform"
            />
            <div v-else class="w-full h-full flex items-center justify-center text-gray-600">
              <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
              </svg>
            </div>
          </div>

          <div class="flex items-start justify-between mb-2">
            <div class="flex-1">
              <h3 class="text-white font-medium group-hover:text-cyan-400 transition-colors">
                {{ window.name }}
              </h3>
              <p class="text-gray-400 text-sm">{{ window.type }}</p>
            </div>
            <div class="flex gap-1">
              <span v-if="window.is_featured" class="text-yellow-400" title="Wyróżnione">⭐</span>
              <span v-if="window.is_active" class="text-green-400" title="Aktywne">✅</span>
              <span v-else class="text-gray-500" title="Nieaktywne">⏸️</span>
            </div>
          </div>

          <div class="text-sm text-gray-400 space-y-1">
            <div v-if="window.model">Model: <span class="text-white">{{ window.model }}</span></div>
            <div v-if="window.manufacturer">Producent: <span class="text-white">{{ window.manufacturer }}</span></div>
            <div class="flex items-center gap-2">
              <span class="text-cyan-400 font-bold">{{ formatPrice(window.price) }} zł</span>
              <span v-if="window.sku" class="text-xs text-gray-500">SKU: {{ window.sku }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const windows = ref([]);
const materials = ref([]);
const loading = ref(false);

const windowsCount = computed(() => windows.value.length);
const activeWindowsCount = computed(() => windows.value.filter(w => w.is_active).length);
const featuredCount = computed(() => windows.value.filter(w => w.is_featured).length);
const materialsCount = computed(() => materials.value.length);

const recentWindows = computed(() => windows.value.slice(0, 6));

const formatPrice = (price) => {
  return new Intl.NumberFormat('pl-PL', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(price);
};

const fetchData = async () => {
  loading.value = true;
  try {
    const token = localStorage.getItem('authToken');
    const headers = { Authorization: `Bearer ${token}` };

    const [windowsRes, materialsRes] = await Promise.all([
      axios.get('http://localhost:8000/api/windows', { headers }),
      axios.get('http://localhost:8000/api/materials', { headers })
    ]);

    windows.value = windowsRes.data.data || windowsRes.data;
    materials.value = materialsRes.data.data || materialsRes.data;
  } catch (error) {
    console.error('Failed to fetch data:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchData();
});
</script>

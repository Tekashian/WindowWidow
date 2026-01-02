<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 p-4 md:p-6">
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-3xl md:text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-purple-500">
          🪟 Zarządzanie typami okien
        </h1>
        <p class="text-gray-400 mt-2">{{ filteredWindows.length }} okien w systemie</p>
      </div>
      <button
        @click="$router.push('/admin/windows/new')"
        class="bg-gradient-to-r from-cyan-600 to-cyan-500 hover:from-cyan-500 hover:to-cyan-400 text-white font-bold py-3 px-6 rounded-xl transition-all transform hover:scale-105 shadow-lg"
      >
        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Dodaj okno
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-gray-800/50 backdrop-blur border border-gray-700 rounded-xl p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm text-gray-400 mb-2">Szukaj</label>
          <input
            v-model="filters.search"
            type="text"
            placeholder="Nazwa, SKU, model..."
            class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-cyan-500 focus:outline-none"
          />
        </div>

        <div>
          <label class="block text-sm text-gray-400 mb-2">Kategoria</label>
          <select
            v-model="filters.category"
            class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-cyan-500 focus:outline-none"
          >
            <option value="">Wszystkie</option>
            <option value="residential">Mieszkaniowe</option>
            <option value="commercial">Komercyjne</option>
            <option value="industrial">Przemysłowe</option>
            <option value="special">Specjalne</option>
          </select>
        </div>

        <div>
          <label class="block text-sm text-gray-400 mb-2">Status</label>
          <select
            v-model="filters.status"
            class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-cyan-500 focus:outline-none"
          >
            <option value="">Wszystkie</option>
            <option value="active">Aktywne</option>
            <option value="inactive">Nieaktywne</option>
            <option value="featured">Wyróżnione</option>
          </select>
        </div>

        <div>
          <label class="block text-sm text-gray-400 mb-2">Sortowanie</label>
          <select
            v-model="filters.sort"
            class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-cyan-500 focus:outline-none"
          >
            <option value="newest">Najnowsze</option>
            <option value="oldest">Najstarsze</option>
            <option value="name_asc">Nazwa A-Z</option>
            <option value="name_desc">Nazwa Z-A</option>
            <option value="price_asc">Cena rosnąco</option>
            <option value="price_desc">Cena malejąco</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Windows Grid -->
    <div v-if="loading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-cyan-500"></div>
      <p class="text-gray-400 mt-4">Ładowanie okien...</p>
    </div>

    <div v-else-if="filteredWindows.length === 0" class="text-center py-12">
      <svg class="w-24 h-24 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
      </svg>
      <p class="text-gray-400 text-lg">Nie znaleziono okien</p>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="window in filteredWindows"
        :key="window.id"
        class="bg-gray-800/50 backdrop-blur border border-gray-700 rounded-xl overflow-hidden hover:border-cyan-500 transition-all group"
      >
        <!-- Image -->
        <div class="aspect-video bg-gray-900 relative overflow-hidden">
          <img
            v-if="window.image_url"
            :src="window.image_url"
            :alt="window.name"
            class="w-full h-full object-cover group-hover:scale-110 transition-transform"
          />
          <div v-else class="w-full h-full flex items-center justify-center text-gray-600">
            <svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
          </div>

          <!-- Badges -->
          <div class="absolute top-2 right-2 flex gap-2">
            <span v-if="window.is_featured" class="bg-yellow-500 text-black text-xs font-bold px-2 py-1 rounded">
              ⭐ Wyróżnione
            </span>
            <span v-if="window.is_active" class="bg-green-500 text-white text-xs font-bold px-2 py-1 rounded">
              ✅ Aktywne
            </span>
            <span v-else class="bg-gray-600 text-white text-xs font-bold px-2 py-1 rounded">
              ⏸️ Nieaktywne
            </span>
          </div>
        </div>

        <!-- Content -->
        <div class="p-4">
          <div class="flex items-start justify-between mb-3">
            <div class="flex-1">
              <h3 class="text-white font-bold text-lg group-hover:text-cyan-400 transition-colors">
                {{ window.name }}
              </h3>
              <p class="text-gray-400 text-sm">{{ window.type }}</p>
            </div>
          </div>

          <!-- Specifications -->
          <div class="space-y-2 mb-4 text-sm">
            <div v-if="window.model" class="flex justify-between">
              <span class="text-gray-400">Model:</span>
              <span class="text-white">{{ window.model }}</span>
            </div>
            <div v-if="window.manufacturer" class="flex justify-between">
              <span class="text-gray-400">Producent:</span>
              <span class="text-white">{{ window.manufacturer }}</span>
            </div>
            <div v-if="window.chambers" class="flex justify-between">
              <span class="text-gray-400">Komory:</span>
              <span class="text-white">{{ window.chambers }}</span>
            </div>
            <div v-if="window.thermal_coefficient" class="flex justify-between">
              <span class="text-gray-400">Wsp. U:</span>
              <span class="text-white">{{ window.thermal_coefficient }} W/m²K</span>
            </div>
            <div v-if="window.sound_insulation" class="flex justify-between">
              <span class="text-gray-400">Izolacja:</span>
              <span class="text-white">{{ window.sound_insulation }} dB</span>
            </div>
            <div v-if="window.sku" class="flex justify-between">
              <span class="text-gray-400">SKU:</span>
              <span class="text-white font-mono text-xs">{{ window.sku }}</span>
            </div>
          </div>

          <!-- Price -->
          <div class="flex items-center justify-between mb-4 py-3 border-t border-gray-700">
            <div>
              <div class="text-cyan-400 font-bold text-2xl">
                {{ formatPrice(window.price) }} zł
              </div>
              <div v-if="window.installation_price" class="text-gray-400 text-xs">
                + {{ formatPrice(window.installation_price) }} zł montaż
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex gap-2">
            <button
              @click="$router.push(`/admin/windows/${window.id}`)"
              class="flex-1 bg-purple-600 hover:bg-purple-500 text-white font-medium py-2 px-4 rounded-lg transition-colors"
            >
              ✏️ Edytuj
            </button>
            <button
              @click="confirmDelete(window)"
              class="bg-red-600 hover:bg-red-500 text-white font-medium py-2 px-4 rounded-lg transition-colors"
            >
              🗑️
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div
      v-if="deleteModal.show"
      class="fixed inset-0 bg-black/70 backdrop-blur flex items-center justify-center z-50 p-4"
      @click.self="deleteModal.show = false"
    >
      <div class="bg-gray-800 border border-red-500 rounded-xl p-6 max-w-md w-full">
        <h3 class="text-xl font-bold text-white mb-4">🗑️ Usuń okno</h3>
        <p class="text-gray-300 mb-6">
          Czy na pewno chcesz usunąć okno <strong class="text-cyan-400">{{ deleteModal.window?.name }}</strong>?
          Tej operacji nie można cofnąć.
        </p>
        <div class="flex gap-3">
          <button
            @click="deleteModal.show = false"
            class="flex-1 bg-gray-700 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-lg transition-colors"
          >
            Anuluj
          </button>
          <button
            @click="deleteWindow"
            :disabled="deleteModal.loading"
            class="flex-1 bg-red-600 hover:bg-red-500 text-white font-medium py-2 px-4 rounded-lg transition-colors disabled:opacity-50"
          >
            {{ deleteModal.loading ? 'Usuwanie...' : 'Usuń' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

const router = useRouter();
const windows = ref([]);
const loading = ref(false);

const filters = ref({
  search: '',
  category: '',
  status: '',
  sort: 'newest'
});

const deleteModal = ref({
  show: false,
  window: null,
  loading: false
});

const filteredWindows = computed(() => {
  let result = [...windows.value];

  // Search filter
  if (filters.value.search) {
    const search = filters.value.search.toLowerCase();
    result = result.filter(w =>
      w.name?.toLowerCase().includes(search) ||
      w.sku?.toLowerCase().includes(search) ||
      w.model?.toLowerCase().includes(search) ||
      w.manufacturer?.toLowerCase().includes(search)
    );
  }

  // Category filter
  if (filters.value.category) {
    result = result.filter(w => w.category === filters.value.category);
  }

  // Status filter
  if (filters.value.status) {
    if (filters.value.status === 'active') {
      result = result.filter(w => w.is_active);
    } else if (filters.value.status === 'inactive') {
      result = result.filter(w => !w.is_active);
    } else if (filters.value.status === 'featured') {
      result = result.filter(w => w.is_featured);
    }
  }

  // Sorting
  switch (filters.value.sort) {
    case 'newest':
      result.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
      break;
    case 'oldest':
      result.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
      break;
    case 'name_asc':
      result.sort((a, b) => (a.name || '').localeCompare(b.name || ''));
      break;
    case 'name_desc':
      result.sort((a, b) => (b.name || '').localeCompare(a.name || ''));
      break;
    case 'price_asc':
      result.sort((a, b) => (a.price || 0) - (b.price || 0));
      break;
    case 'price_desc':
      result.sort((a, b) => (b.price || 0) - (a.price || 0));
      break;
  }

  return result;
});

const formatPrice = (price) => {
  return new Intl.NumberFormat('pl-PL', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(price);
};

const fetchWindows = async () => {
  loading.value = true;
  try {
    const token = localStorage.getItem('authToken');
    const response = await axios.get('http://localhost:8000/api/windows', {
      headers: { Authorization: `Bearer ${token}` }
    });
    windows.value = response.data.data || response.data;
  } catch (error) {
    console.error('Failed to fetch windows:', error);
  } finally {
    loading.value = false;
  }
};

const confirmDelete = (window) => {
  deleteModal.value.show = true;
  deleteModal.value.window = window;
};

const deleteWindow = async () => {
  deleteModal.value.loading = true;
  try {
    const token = localStorage.getItem('authToken');
    await axios.delete(`http://localhost:8000/api/windows/${deleteModal.value.window.id}`, {
      headers: { Authorization: `Bearer ${token}` }
    });
    
    windows.value = windows.value.filter(w => w.id !== deleteModal.value.window.id);
    deleteModal.value.show = false;
    deleteModal.value.window = null;
  } catch (error) {
    console.error('Failed to delete window:', error);
    alert('Nie udało się usunąć okna. Sprawdź czy okno nie jest używane w zamówieniach.');
  } finally {
    deleteModal.value.loading = false;
  }
};

onMounted(() => {
  fetchWindows();
});
</script>

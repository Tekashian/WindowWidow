<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 p-4 md:p-6">
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
      <div>
        <button
          @click="$router.back()"
          class="text-gray-400 hover:text-cyan-400 mb-2 flex items-center gap-2 transition-colors"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
          Powrót
        </button>
        <h1 class="text-3xl md:text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-purple-500">
          {{ isEdit ? '✏️ Edycja okna' : '➕ Nowe okno' }}
        </h1>
        <p class="text-gray-400 mt-2">{{ isEdit ? 'Zaktualizuj dane okna' : 'Dodaj nowy typ okna do katalogu' }}</p>
      </div>
    </div>

    <!-- Form -->
    <form @submit.prevent="saveWindow" class="max-w-4xl">
      <!-- Image Upload -->
      <div class="bg-gray-800/50 backdrop-blur border border-gray-700 rounded-xl p-6 mb-6">
        <h2 class="text-xl font-bold text-cyan-400 mb-4">📷 Zdjęcie okna</h2>
        
        <ImageUpload
          v-model="form.image_url"
          :preview="form.image_url"
          @update:modelValue="form.image_url = $event"
        />
      </div>

      <!-- Basic Information -->
      <div class="bg-gray-800/50 backdrop-blur border border-gray-700 rounded-xl p-6 mb-6">
        <h2 class="text-xl font-bold text-cyan-400 mb-4">📝 Podstawowe informacje</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm text-gray-400 mb-2">Nazwa okna *</label>
            <input
              v-model="form.name"
              type="text"
              required
              placeholder="np. Okno PCV 3-szybowe energooszczędne"
              class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-cyan-500 focus:outline-none"
            />
          </div>

          <div>
            <label class="block text-sm text-gray-400 mb-2">Typ okna *</label>
            <select
              v-model="form.type"
              required
              class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-cyan-500 focus:outline-none"
            >
              <option value="">Wybierz typ</option>
              <option value="PCV">PCV</option>
              <option value="Drewniane">Drewniane</option>
              <option value="Aluminiowe">Aluminiowe</option>
              <option value="Drewniano-aluminiowe">Drewniano-aluminiowe</option>
            </select>
          </div>

          <div>
            <label class="block text-sm text-gray-400 mb-2">Model</label>
            <input
              v-model="form.model"
              type="text"
              placeholder="np. Premium Plus 2024"
              class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-cyan-500 focus:outline-none"
            />
          </div>

          <div>
            <label class="block text-sm text-gray-400 mb-2">Producent</label>
            <input
              v-model="form.manufacturer"
              type="text"
              placeholder="np. Rehau, Veka, Schuco"
              class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-cyan-500 focus:outline-none"
            />
          </div>

          <div>
            <label class="block text-sm text-gray-400 mb-2">SKU (kod produktu)</label>
            <input
              v-model="form.sku"
              type="text"
              placeholder="np. WIN-PCV-001"
              class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-cyan-500 focus:outline-none font-mono"
            />
          </div>

          <div>
            <label class="block text-sm text-gray-400 mb-2">Kategoria</label>
            <select
              v-model="form.category"
              class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-cyan-500 focus:outline-none"
            >
              <option value="">Wybierz kategorię</option>
              <option value="residential">Mieszkaniowe</option>
              <option value="commercial">Komercyjne</option>
              <option value="industrial">Przemysłowe</option>
              <option value="special">Specjalne</option>
            </select>
          </div>
        </div>

        <div class="mt-4">
          <label class="block text-sm text-gray-400 mb-2">Opis</label>
          <textarea
            v-model="form.description"
            rows="3"
            placeholder="Szczegółowy opis okna, cechy, zastosowanie..."
            class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-cyan-500 focus:outline-none resize-none"
          ></textarea>
        </div>
      </div>

      <!-- Dimensions -->
      <div class="bg-gray-800/50 backdrop-blur border border-gray-700 rounded-xl p-6 mb-6">
        <h2 class="text-xl font-bold text-cyan-400 mb-4">📐 Wymiary (cm)</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm text-gray-400 mb-2">Szerokość *</label>
            <input
              v-model.number="form.width"
              type="number"
              step="0.01"
              required
              placeholder="np. 120.50"
              class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-cyan-500 focus:outline-none"
            />
          </div>

          <div>
            <label class="block text-sm text-gray-400 mb-2">Wysokość *</label>
            <input
              v-model.number="form.height"
              type="number"
              step="0.01"
              required
              placeholder="np. 150.00"
              class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-cyan-500 focus:outline-none"
            />
          </div>

          <div>
            <label class="block text-sm text-gray-400 mb-2">Głębokość</label>
            <input
              v-model.number="form.depth"
              type="number"
              step="0.01"
              placeholder="np. 7.00"
              class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-cyan-500 focus:outline-none"
            />
          </div>
        </div>
      </div>

      <!-- Technical Specifications -->
      <div class="bg-gray-800/50 backdrop-blur border border-gray-700 rounded-xl p-6 mb-6">
        <h2 class="text-xl font-bold text-cyan-400 mb-4">🔧 Specyfikacja techniczna</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm text-gray-400 mb-2">Liczba komór profilu</label>
            <input
              v-model.number="form.chambers"
              type="number"
              min="3"
              max="7"
              placeholder="np. 5"
              class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-cyan-500 focus:outline-none"
            />
            <p class="text-xs text-gray-500 mt-1">Typowo: 3, 5 lub 7 komór</p>
          </div>

          <div>
            <label class="block text-sm text-gray-400 mb-2">Współczynnik U (W/m²K)</label>
            <input
              v-model.number="form.thermal_coefficient"
              type="number"
              step="0.001"
              placeholder="np. 0.900"
              class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-cyan-500 focus:outline-none"
            />
            <p class="text-xs text-gray-500 mt-1">Im niższy, tym lepiej (0.7-1.2)</p>
          </div>

          <div>
            <label class="block text-sm text-gray-400 mb-2">Izolacja akustyczna (dB)</label>
            <input
              v-model.number="form.sound_insulation"
              type="number"
              min="20"
              max="50"
              placeholder="np. 35"
              class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-cyan-500 focus:outline-none"
            />
            <p class="text-xs text-gray-500 mt-1">Typowo: 30-45 dB Rw</p>
          </div>
        </div>
      </div>

      <!-- Features & Tags -->
      <div class="bg-gray-800/50 backdrop-blur border border-gray-700 rounded-xl p-6 mb-6">
        <h2 class="text-xl font-bold text-cyan-400 mb-4">⭐ Cechy i tagi</h2>
        
        <div class="mb-4">
          <label class="block text-sm text-gray-400 mb-2">Cechy dodatkowe</label>
          <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
            <label v-for="feature in availableFeatures" :key="feature" class="flex items-center gap-2 text-white cursor-pointer">
              <input
                type="checkbox"
                :value="feature"
                v-model="selectedFeatures"
                class="rounded text-cyan-500 focus:ring-cyan-500"
              />
              {{ feature }}
            </label>
          </div>
        </div>

        <div>
          <label class="block text-sm text-gray-400 mb-2">Tagi (oddziel przecinkami)</label>
          <input
            v-model="tagsInput"
            type="text"
            placeholder="np. energooszczędne, antywłamaniowe, cichy"
            class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-cyan-500 focus:outline-none"
          />
          <div v-if="parsedTags.length" class="flex flex-wrap gap-2 mt-2">
            <span
              v-for="tag in parsedTags"
              :key="tag"
              class="bg-purple-600 text-white text-xs px-2 py-1 rounded"
            >
              {{ tag }}
            </span>
          </div>
        </div>
      </div>

      <!-- Pricing -->
      <div class="bg-gray-800/50 backdrop-blur border border-gray-700 rounded-xl p-6 mb-6">
        <h2 class="text-xl font-bold text-cyan-400 mb-4">💰 Cennik</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm text-gray-400 mb-2">Cena produktu *</label>
            <input
              v-model.number="form.price"
              type="number"
              step="0.01"
              required
              placeholder="np. 1500.00"
              class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-cyan-500 focus:outline-none"
            />
          </div>

          <div>
            <label class="block text-sm text-gray-400 mb-2">Cena bazowa</label>
            <input
              v-model.number="form.base_price"
              type="number"
              step="0.01"
              placeholder="np. 1200.00"
              class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-cyan-500 focus:outline-none"
            />
            <p class="text-xs text-gray-500 mt-1">Cena przed marżą</p>
          </div>

          <div>
            <label class="block text-sm text-gray-400 mb-2">Cena montażu</label>
            <input
              v-model.number="form.installation_price"
              type="number"
              step="0.01"
              placeholder="np. 300.00"
              class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-cyan-500 focus:outline-none"
            />
          </div>
        </div>
      </div>

      <!-- Status & Visibility -->
      <div class="bg-gray-800/50 backdrop-blur border border-gray-700 rounded-xl p-6 mb-6">
        <h2 class="text-xl font-bold text-cyan-400 mb-4">👁️ Widoczność i status</h2>
        
        <div class="space-y-3">
          <label class="flex items-center gap-3 text-white cursor-pointer">
            <input
              type="checkbox"
              v-model="form.is_active"
              class="w-5 h-5 rounded text-green-500 focus:ring-green-500"
            />
            <div>
              <div class="font-medium">Aktywne</div>
              <div class="text-sm text-gray-400">Okno widoczne w katalogu i dostępne do zamówienia</div>
            </div>
          </label>

          <label class="flex items-center gap-3 text-white cursor-pointer">
            <input
              type="checkbox"
              v-model="form.is_featured"
              class="w-5 h-5 rounded text-yellow-500 focus:ring-yellow-500"
            />
            <div>
              <div class="font-medium">Wyróżnione</div>
              <div class="text-sm text-gray-400">Okno będzie wyświetlane na głównej stronie i w promocjach</div>
            </div>
          </label>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex gap-4">
        <button
          type="button"
          @click="$router.back()"
          class="flex-1 bg-gray-700 hover:bg-gray-600 text-white font-bold py-3 px-6 rounded-xl transition-colors"
        >
          Anuluj
        </button>
        <button
          type="submit"
          :disabled="loading"
          class="flex-1 bg-gradient-to-r from-cyan-600 to-purple-600 hover:from-cyan-500 hover:to-purple-500 text-white font-bold py-3 px-6 rounded-xl transition-all transform hover:scale-105 shadow-lg disabled:opacity-50"
        >
          {{ loading ? 'Zapisywanie...' : (isEdit ? '💾 Zapisz zmiany' : '➕ Dodaj okno') }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import ImageUpload from './components/ImageUpload.vue';

const route = useRoute();
const router = useRouter();

const isEdit = computed(() => !!route.params.id);
const loading = ref(false);

const form = ref({
  name: '',
  type: '',
  model: '',
  manufacturer: '',
  sku: '',
  category: '',
  description: '',
  width: null,
  height: null,
  depth: null,
  chambers: null,
  thermal_coefficient: null,
  sound_insulation: null,
  price: null,
  base_price: null,
  installation_price: null,
  is_active: true,
  is_featured: false,
  image_url: '',
  features: [],
  tags: []
});

const availableFeatures = [
  'Antywłamaniowe',
  'Energooszczędne',
  'Wyciszające',
  'Bezpieczne dla dzieci',
  'Odporne na warunki atmosferyczne',
  'Łatwe w konserwacji'
];

const selectedFeatures = ref([]);
const tagsInput = ref('');

const parsedTags = computed(() => {
  return tagsInput.value
    .split(',')
    .map(t => t.trim())
    .filter(t => t.length > 0);
});

const fetchWindow = async () => {
  if (!isEdit.value) return;

  loading.value = true;
  try {
    const token = localStorage.getItem('authToken');
    const response = await axios.get(`http://localhost:8000/api/windows/${route.params.id}`, {
      headers: { Authorization: `Bearer ${token}` }
    });
    
    const data = response.data.data || response.data;
    Object.assign(form.value, data);
    
    // Parse features and tags
    if (data.features) {
      selectedFeatures.value = typeof data.features === 'string' 
        ? JSON.parse(data.features) 
        : data.features;
    }
    
    if (data.tags) {
      const tags = typeof data.tags === 'string' ? JSON.parse(data.tags) : data.tags;
      tagsInput.value = tags.join(', ');
    }
  } catch (error) {
    console.error('Failed to fetch window:', error);
    alert('Nie udało się pobrać danych okna');
    router.back();
  } finally {
    loading.value = false;
  }
};

const saveWindow = async () => {
  loading.value = true;
  try {
    const token = localStorage.getItem('authToken');
    
    const payload = {
      ...form.value,
      features: selectedFeatures.value,
      tags: parsedTags.value
    };

    if (isEdit.value) {
      await axios.put(`http://localhost:8000/api/windows/${route.params.id}`, payload, {
        headers: { Authorization: `Bearer ${token}` }
      });
    } else {
      await axios.post('http://localhost:8000/api/windows', payload, {
        headers: { Authorization: `Bearer ${token}` }
      });
    }

    router.push('/admin/windows');
  } catch (error) {
    console.error('Failed to save window:', error);
    alert('Nie udało się zapisać okna: ' + (error.response?.data?.message || error.message));
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  if (isEdit.value) {
    fetchWindow();
  }
});
</script>

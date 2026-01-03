<template>
  <div class="dashboard-container">
    <div class="dashboard-header">
      <h1 class="dashboard-title">⚙️ Panel Administracyjny</h1>
      <p class="dashboard-subtitle">Zarządzanie systemem WindowWidow</p>
    </div>

    <div class="stats-grid">
      <div class="stat-card stat-cyan">
        <div class="stat-label">Typy okien</div>
        <div class="stat-value">{{ windowsCount }}</div>
      </div>

      <div class="stat-card stat-purple">
        <div class="stat-label">Aktywne</div>
        <div class="stat-value">{{ activeWindowsCount }}</div>
      </div>

      <div class="stat-card stat-green">
        <div class="stat-label">Wyróżnione</div>
        <div class="stat-value">{{ featuredCount }}</div>
      </div>

      <div class="stat-card stat-blue">
        <div class="stat-label">Materiały</div>
        <div class="stat-value">{{ materialsCount }}</div>
      </div>
    </div>

    <div class="actions-grid">
      <button @click="$router.push('/admin/windows/new')" class="action-btn btn-cyan">
        <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Dodaj typ okna
      </button>

      <button @click="$router.push('/admin/windows')" class="action-btn btn-purple">
        <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
        </svg>
        Zarządzaj oknami
      </button>

      <button @click="$router.push('/materials')" class="action-btn btn-blue">
        <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
        </svg>
        Zarządzaj materiałami
      </button>
    </div>

    <div class="content-section">
      <h2 class="section-title">🪟 Ostatnio dodane okna</h2>
      
      <div v-if="recentWindows.length === 0" class="empty-state">
        Brak okien w systemie. Dodaj pierwsze okno!
      </div>

      <div v-else class="windows-grid">
        <div
          v-for="window in recentWindows"
          :key="window.id"
          @click="$router.push(`/admin/windows/${window.id}`)"
          class="window-card"
        >
          <div class="window-image">
            <img
              v-if="window.image_url"
              :src="window.image_url"
              :alt="window.name"
              class="image"
            />
            <div v-else class="image-placeholder">
              <svg class="placeholder-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
              </svg>
            </div>
          </div>

          <div class="window-header">
            <div class="window-info">
              <h3 class="window-name">{{ window.name }}</h3>
              <p class="window-type">{{ window.type }}</p>
            </div>
            <div class="window-badges">
              <span v-if="window.is_featured" class="badge-featured" title="Wyróżnione">⭐</span>
              <span v-if="window.is_active" class="badge-active" title="Aktywne">✅</span>
              <span v-else class="badge-inactive" title="Nieaktywne">⏸️</span>
            </div>
          </div>

          <div class="window-details">
            <div v-if="window.model" class="detail-row">Model: <span>{{ window.model }}</span></div>
            <div v-if="window.manufacturer" class="detail-row">Producent: <span>{{ window.manufacturer }}</span></div>
            <div class="detail-price">
              <span class="price">{{ formatPrice(window.price) }} zł</span>
              <span v-if="window.sku" class="sku">SKU: {{ window.sku }}</span>
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
    const token = localStorage.getItem('token');
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

<style scoped>
.dashboard-container {
  min-height: 100vh;
  padding: 1.5rem;
  background: linear-gradient(135deg, var(--darker) 0%, var(--dark) 50%, var(--darker) 100%);
}

.dashboard-header {
  margin-bottom: 2rem;
}

.dashboard-title {
  font-size: 2.5rem;
  font-weight: 700;
  background: var(--gradient-primary);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin-bottom: 0.5rem;
}

.dashboard-subtitle {
  color: var(--gray-400);
  font-size: 1.125rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  border: 1px solid;
  border-radius: 1rem;
  padding: 1.5rem;
  transition: all var(--transition-base);
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 30px rgba(0, 245, 255, 0.2);
}

.stat-cyan {
  border-color: rgba(0, 245, 255, 0.3);
}

.stat-cyan:hover {
  border-color: var(--primary);
}

.stat-purple {
  border-color: rgba(124, 58, 237, 0.3);
}

.stat-purple:hover {
  border-color: var(--secondary);
}

.stat-green {
  border-color: rgba(16, 185, 129, 0.3);
}

.stat-green:hover {
  border-color: var(--success);
}

.stat-blue {
  border-color: rgba(59, 130, 246, 0.3);
}

.stat-blue:hover {
  border-color: #3B82F6;
}

.stat-label {
  color: var(--gray-400);
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.stat-value {
  font-size: 2rem;
  font-weight: 700;
  color: var(--primary);
}

.actions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
}

.action-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  padding: 1.25rem 1.5rem;
  border: none;
  border-radius: 0.75rem;
  color: white;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  transition: all var(--transition-base);
  box-shadow: var(--shadow-lg);
}

.action-btn:hover {
  transform: translateY(-2px) scale(1.02);
  box-shadow: var(--shadow-xl);
}

.btn-cyan {
  background: linear-gradient(135deg, #00D4E0 0%, var(--primary) 100%);
}

.btn-purple {
  background: linear-gradient(135deg, #7C3AED 0%, #A855F7 100%);
}

.btn-blue {
  background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
}

.btn-icon {
  width: 1.5rem;
  height: 1.5rem;
}

.content-section {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  border: 1px solid var(--border);
  border-radius: 1rem;
  padding: 2rem;
}

.section-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--primary);
  margin-bottom: 1.5rem;
}

.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  color: var(--gray-400);
  font-size: 1.125rem;
}

.windows-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.5rem;
}

.window-card {
  background: rgba(0, 0, 0, 0.3);
  border: 1px solid var(--border);
  border-radius: 0.75rem;
  padding: 1rem;
  cursor: pointer;
  transition: all var(--transition-base);
}

.window-card:hover {
  border-color: var(--primary);
  transform: translateY(-4px);
  box-shadow: var(--shadow-glow);
}

.window-image {
  aspect-ratio: 16 / 9;
  background: rgba(0, 0, 0, 0.5);
  border-radius: 0.5rem;
  margin-bottom: 1rem;
  overflow: hidden;
}

.image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform var(--transition-base);
}

.window-card:hover .image {
  transform: scale(1.1);
}

.image-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--gray-600);
}

.placeholder-icon {
  width: 4rem;
  height: 4rem;
}

.window-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 0.75rem;
}

.window-info {
  flex: 1;
}

.window-name {
  color: white;
  font-weight: 600;
  font-size: 1.125rem;
  margin-bottom: 0.25rem;
  transition: color var(--transition-fast);
}

.window-card:hover .window-name {
  color: var(--primary);
}

.window-type {
  color: var(--gray-400);
  font-size: 0.875rem;
}

.window-badges {
  display: flex;
  gap: 0.25rem;
  font-size: 1.25rem;
}

.badge-featured {
  color: #FBBF24;
}

.badge-active {
  color: var(--success);
}

.badge-inactive {
  color: var(--gray-500);
}

.window-details {
  font-size: 0.875rem;
  color: var(--gray-400);
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.detail-row span {
  color: white;
  font-weight: 500;
}

.detail-price {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
}

.price {
  color: var(--primary);
  font-weight: 700;
  font-size: 1rem;
}

.sku {
  color: var(--gray-500);
  font-size: 0.75rem;
}

@media (max-width: 768px) {
  .dashboard-container {
    padding: 1rem;
  }

  .dashboard-title {
    font-size: 2rem;
  }

  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
  }

  .actions-grid {
    grid-template-columns: 1fr;
  }

  .windows-grid {
    grid-template-columns: 1fr;
  }
}
</style>

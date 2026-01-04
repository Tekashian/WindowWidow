<template>
  <div class="materials-container">
    <div class="page-header">
      <h1>📦 Zarządzanie Materiałami</h1>
      <button @click="showAddModal = true" class="btn btn-primary">
        ➕ Dodaj Materiał
      </button>
    </div>

    <div class="materials-grid">
      <div v-for="material in materials" :key="material.id" class="material-card">
        <div class="material-header">
          <h3>{{ material.name }}</h3>
          <span :class="['stock-badge', getStockClass(material)]">
            {{ material.current_stock }} {{ material.unit }}
          </span>
        </div>
        
        <div class="material-info">
          <p><strong>Typ:</strong> {{ material.type }}</p>
          <p><strong>Min. stan:</strong> {{ material.minimum_stock }} {{ material.unit }}</p>
          <p v-if="material.supplier"><strong>Dostawca:</strong> {{ material.supplier }}</p>
        </div>

        <div class="material-actions">
          <button @click="openAddStockModal(material)" class="btn btn-sm btn-success">
            ➕ Dodaj
          </button>
          <button @click="openRemoveStockModal(material)" class="btn btn-sm btn-warning">
            ➖ Usuń
          </button>
          <button @click="editMaterial(material)" class="btn btn-sm btn-secondary">
            ✏️ Edytuj
          </button>
        </div>
      </div>
    </div>

    <!-- Modal dodawania materiału -->
    <div v-if="showAddModal" class="modal-overlay" @click="showAddModal = false">
      <div class="modal-content" @click.stop>
        <h2>Dodaj Nowy Materiał</h2>
        <form @submit.prevent="addMaterial">
          <div class="form-group">
            <label>Nazwa:</label>
            <input v-model="newMaterial.name" required class="form-input" />
          </div>
          
          <div class="form-group">
            <label>Typ:</label>
            <select v-model="newMaterial.type" required class="form-input">
              <option value="profil">Profil</option>
              <option value="szyba">Szyba</option>
              <option value="okucie">Okucie</option>
              <option value="inne">Inne</option>
            </select>
          </div>

          <div class="form-group">
            <label>Jednostka:</label>
            <input v-model="newMaterial.unit" required class="form-input" placeholder="np. m, szt, kg" />
          </div>

          <div class="form-group">
            <label>Stan początkowy:</label>
            <input v-model.number="newMaterial.current_stock" type="number" step="0.01" required class="form-input" />
          </div>

          <div class="form-group">
            <label>Minimalny stan:</label>
            <input v-model.number="newMaterial.minimum_stock" type="number" step="0.01" required class="form-input" />
          </div>

          <div class="form-group">
            <label>Dostawca (opcjonalnie):</label>
            <input v-model="newMaterial.supplier" class="form-input" />
          </div>

          <div class="modal-actions">
            <button type="submit" class="btn btn-primary">Dodaj</button>
            <button type="button" @click="showAddModal = false" class="btn btn-secondary">Anuluj</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal dodawania stanu -->
    <div v-if="showAddStockModal" class="modal-overlay" @click="showAddStockModal = false">
      <div class="modal-content" @click.stop>
        <h2>Dodaj Stan: {{ selectedMaterial?.name }}</h2>
        <form @submit.prevent="addStock">
          <div class="form-group">
            <label>Ilość:</label>
            <input v-model.number="stockForm.quantity" type="number" step="0.01" required class="form-input" />
          </div>

          <div class="form-group">
            <label>Powód:</label>
            <input v-model="stockForm.reason" required class="form-input" placeholder="np. Dostawa od dostawcy" />
          </div>

          <div class="modal-actions">
            <button type="submit" class="btn btn-success">Dodaj</button>
            <button type="button" @click="showAddStockModal = false" class="btn btn-secondary">Anuluj</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal usuwania stanu -->
    <div v-if="showRemoveStockModal" class="modal-overlay" @click="showRemoveStockModal = false">
      <div class="modal-content" @click.stop>
        <h2>Usuń Stan: {{ selectedMaterial?.name }}</h2>
        <form @submit.prevent="removeStock">
          <div class="form-group">
            <label>Ilość:</label>
            <input v-model.number="stockForm.quantity" type="number" step="0.01" required class="form-input" />
          </div>

          <div class="form-group">
            <label>Powód:</label>
            <input v-model="stockForm.reason" required class="form-input" placeholder="np. Zużycie produkcyjne" />
          </div>

          <div class="modal-actions">
            <button type="submit" class="btn btn-warning">Usuń</button>
            <button type="button" @click="showRemoveStockModal = false" class="btn btn-secondary">Anuluj</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const API_BASE = 'http://localhost:8000/api';
const materials = ref([]);
const showAddModal = ref(false);
const showAddStockModal = ref(false);
const showRemoveStockModal = ref(false);
const selectedMaterial = ref(null);

const newMaterial = ref({
  name: '',
  type: 'profil',
  unit: 'm',
  current_stock: 0,
  minimum_stock: 10,
  supplier: ''
});

const stockForm = ref({
  quantity: 0,
  reason: ''
});

const getAuthHeaders = () => {
  const token = localStorage.getItem('auth_token');
  return { Authorization: `Bearer ${token}` };
};

const fetchMaterials = async () => {
  try {
    const response = await axios.get(`${API_BASE}/materials`, {
      headers: getAuthHeaders()
    });
    materials.value = response.data;
  } catch (error) {
    console.error('Failed to fetch materials:', error);
    alert('Nie udało się pobrać materiałów');
  }
};

const addMaterial = async () => {
  try {
    await axios.post(`${API_BASE}/materials`, newMaterial.value, {
      headers: getAuthHeaders()
    });
    
    showAddModal.value = false;
    newMaterial.value = {
      name: '',
      type: 'profil',
      unit: 'm',
      current_stock: 0,
      minimum_stock: 10,
      supplier: ''
    };
    
    await fetchMaterials();
    alert('Materiał dodany pomyślnie');
  } catch (error) {
    console.error('Failed to add material:', error);
    alert('Nie udało się dodać materiału');
  }
};

const openAddStockModal = (material) => {
  selectedMaterial.value = material;
  stockForm.value = { quantity: 0, reason: '' };
  showAddStockModal.value = true;
};

const openRemoveStockModal = (material) => {
  selectedMaterial.value = material;
  stockForm.value = { quantity: 0, reason: '' };
  showRemoveStockModal.value = true;
};

const addStock = async () => {
  try {
    await axios.post(
      `${API_BASE}/materials/${selectedMaterial.value.id}/add-stock`,
      stockForm.value,
      { headers: getAuthHeaders() }
    );
    
    showAddStockModal.value = false;
    await fetchMaterials();
    alert('Stan zwiększony pomyślnie');
  } catch (error) {
    console.error('Failed to add stock:', error);
    alert('Nie udało się zwiększyć stanu');
  }
};

const removeStock = async () => {
  try {
    await axios.post(
      `${API_BASE}/materials/${selectedMaterial.value.id}/remove-stock`,
      stockForm.value,
      { headers: getAuthHeaders() }
    );
    
    showRemoveStockModal.value = false;
    await fetchMaterials();
    alert('Stan zmniejszony pomyślnie');
  } catch (error) {
    console.error('Failed to remove stock:', error);
    alert(error.response?.data?.error || 'Nie udało się zmniejszyć stanu');
  }
};

const editMaterial = (material) => {
  alert('Funkcja edycji w przygotowaniu');
};

const getStockClass = (material) => {
  if (material.current_stock <= material.minimum_stock) return 'stock-low';
  if (material.current_stock <= material.minimum_stock * 1.5) return 'stock-warning';
  return 'stock-ok';
};

onMounted(() => {
  fetchMaterials();
});
</script>

<style scoped>
.materials-container {
  padding: 20px;
  max-width: 1400px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
}

.page-header h1 {
  font-size: 2rem;
  color: #2c3e50;
}

.materials-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
}

.material-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s, box-shadow 0.2s;
}

.material-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

.material-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
  padding-bottom: 15px;
  border-bottom: 2px solid #ecf0f1;
}

.material-header h3 {
  margin: 0;
  color: #2c3e50;
  font-size: 1.2rem;
}

.stock-badge {
  padding: 6px 12px;
  border-radius: 20px;
  font-weight: bold;
  font-size: 0.9rem;
}

.stock-ok {
  background: #d4edda;
  color: #155724;
}

.stock-warning {
  background: #fff3cd;
  color: #856404;
}

.stock-low {
  background: #f8d7da;
  color: #721c24;
}

.material-info {
  margin-bottom: 15px;
}

.material-info p {
  margin: 8px 0;
  color: #555;
  font-size: 0.95rem;
}

.material-actions {
  display: flex;
  gap: 8px;
  justify-content: flex-end;
}

.btn {
  padding: 10px 20px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.2s;
}

.btn-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-sm {
  padding: 6px 12px;
  font-size: 0.85rem;
}

.btn-success {
  background: #28a745;
  color: white;
}

.btn-success:hover {
  background: #218838;
}

.btn-warning {
  background: #ffc107;
  color: #000;
}

.btn-warning:hover {
  background: #e0a800;
}

.btn-secondary {
  background: #6c757d;
  color: white;
}

.btn-secondary:hover {
  background: #5a6268;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  padding: 30px;
  border-radius: 12px;
  max-width: 500px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-content h2 {
  margin-top: 0;
  margin-bottom: 20px;
  color: #2c3e50;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #2c3e50;
}

.form-input {
  width: 100%;
  padding: 10px;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.2s;
}

.form-input:focus {
  outline: none;
  border-color: #667eea;
}

.modal-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  margin-top: 20px;
}
</style>

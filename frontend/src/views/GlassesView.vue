<template>
  <div class="glasses-view">
    <div class="header">
      <h1>Typy Szkła</h1>
      <button @click="showForm = true" class="btn btn-primary">+ Dodaj szkło</button>
    </div>

    <div v-if="loading" class="loading">Ładowanie danych...</div>
    <div v-if="error" class="error">{{ error }}</div>

    <table v-if="!loading && !error && glasses.length > 0" class="table">
      <thead>
        <tr>
          <th>Nazwa</th>
          <th>Typ</th>
          <th>Grubość</th>
          <th>U-value</th>
          <th>Cena/m²</th>
          <th>Opis</th>
          <th>Akcje</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="glass in glasses" :key="glass.id">
          <td>{{ glass.name }}</td>
          <td>{{ glass.type }}</td>
          <td>{{ glass.thickness }}mm</td>
          <td>{{ glass.u_value }}</td>
          <td>{{ glass.price_per_sqm }} zł</td>
          <td>{{ glass.description || '-' }}</td>
          <td>
            <button @click="editGlass(glass)" class="btn btn-secondary">Edytuj</button>
            <button @click="deleteGlass(glass.id)" class="btn btn-danger">Usuń</button>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-if="showForm" class="modal">
      <div class="modal-content card">
        <h2>{{ editingGlass ? 'Edytuj szkło' : 'Dodaj nowe szkło' }}</h2>
        <form @submit.prevent="saveGlass">
          <div class="form-group">
            <label>Nazwa:</label>
            <input v-model="formData.name" required>
          </div>
          <div class="form-group">
            <label>Typ:</label>
            <input v-model="formData.type" required>
          </div>
          <div class="form-group">
            <label>Grubość (mm):</label>
            <input v-model.number="formData.thickness" type="number" required>
          </div>
          <div class="form-group">
            <label>U-value:</label>
            <input v-model.number="formData.u_value" type="number" step="0.01" required>
          </div>
          <div class="form-group">
            <label>Cena za m² (zł):</label>
            <input v-model.number="formData.price_per_sqm" type="number" step="0.01" required>
          </div>
          <div class="form-group">
            <label>Opis:</label>
            <textarea v-model="formData.description" rows="3"></textarea>
          </div>
          <div class="actions">
            <button type="submit" class="btn btn-primary">Zapisz</button>
            <button type="button" @click="closeForm" class="btn btn-secondary">Anuluj</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { glassesAPI } from '../services/api'

const glasses = ref([])
const loading = ref(false)
const error = ref(null)
const showForm = ref(false)
const editingGlass = ref(null)
const formData = ref({
  name: '',
  type: '',
  thickness: 0,
  u_value: 0,
  price_per_sqm: 0,
  description: ''
})

const loadGlasses = async () => {
  loading.value = true
  error.value = null
  try {
    const response = await glassesAPI.getAll()
    glasses.value = response.data
  } catch (err) {
    error.value = 'Nie udało się załadować danych: ' + err.message
  } finally {
    loading.value = false
  }
}

const saveGlass = async () => {
  try {
    if (editingGlass.value) {
      await glassesAPI.update(editingGlass.value.id, formData.value)
    } else {
      await glassesAPI.create(formData.value)
    }
    closeForm()
    loadGlasses()
  } catch (err) {
    error.value = 'Nie udało się zapisać: ' + err.message
  }
}

const editGlass = (glass) => {
  editingGlass.value = glass
  formData.value = { ...glass }
  showForm.value = true
}

const deleteGlass = async (id) => {
  if (confirm('Czy na pewno usunąć to szkło?')) {
    try {
      await glassesAPI.delete(id)
      loadGlasses()
    } catch (err) {
      error.value = 'Nie udało się usunąć: ' + err.message
    }
  }
}

const closeForm = () => {
  showForm.value = false
  editingGlass.value = null
  formData.value = {
    name: '',
    type: '',
    thickness: 0,
    u_value: 0,
    price_per_sqm: 0,
    description: ''
  }
}

onMounted(() => {
  loadGlasses()
})
</script>

<style scoped>
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.actions {
  display: flex;
  gap: 0.5rem;
}

.modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  max-width: 500px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
}
</style>

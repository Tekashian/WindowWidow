<template>
  <div class="profiles-view">
    <div class="header">
      <h1>Profile Okienne</h1>
      <button @click="showForm = true" class="btn btn-primary">+ Dodaj profil</button>
    </div>

    <div v-if="loading" class="loading">Ładowanie danych...</div>
    <div v-if="error" class="error">{{ error }}</div>

    <table v-if="!loading && !error && profiles.length > 0" class="table">
      <thead>
        <tr>
          <th>Nazwa</th>
          <th>Producent</th>
          <th>Typ</th>
          <th>Materiał</th>
          <th>Kolor</th>
          <th>Cena/metr</th>
          <th>Akcje</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="profile in profiles" :key="profile.id">
          <td>{{ profile.name }}</td>
          <td>{{ profile.manufacturer }}</td>
          <td>{{ profile.type }}</td>
          <td>{{ profile.material }}</td>
          <td>{{ profile.color }}</td>
          <td>{{ profile.price_per_meter }} zł</td>
          <td>
            <button @click="editProfile(profile)" class="btn btn-secondary">Edytuj</button>
            <button @click="deleteProfile(profile.id)" class="btn btn-danger">Usuń</button>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-if="showForm" class="modal">
      <div class="modal-content card">
        <h2>{{ editingProfile ? 'Edytuj profil' : 'Dodaj nowy profil' }}</h2>
        <form @submit.prevent="saveProfile">
          <div class="form-group">
            <label>Nazwa:</label>
            <input v-model="formData.name" required>
          </div>
          <div class="form-group">
            <label>Producent:</label>
            <input v-model="formData.manufacturer" required>
          </div>
          <div class="form-group">
            <label>Typ:</label>
            <input v-model="formData.type" required>
          </div>
          <div class="form-group">
            <label>Materiał:</label>
            <input v-model="formData.material" required>
          </div>
          <div class="form-group">
            <label>Kolor:</label>
            <input v-model="formData.color" required>
          </div>
          <div class="form-group">
            <label>Cena za metr (zł):</label>
            <input v-model.number="formData.price_per_meter" type="number" step="0.01" required>
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
import { profilesAPI } from '../services/api'

const profiles = ref([])
const loading = ref(false)
const error = ref(null)
const showForm = ref(false)
const editingProfile = ref(null)
const formData = ref({
  name: '',
  manufacturer: '',
  type: '',
  material: '',
  color: '',
  price_per_meter: 0
})

const loadProfiles = async () => {
  loading.value = true
  error.value = null
  try {
    const response = await profilesAPI.getAll()
    profiles.value = response.data
  } catch (err) {
    error.value = 'Nie udało się załadować danych: ' + err.message
  } finally {
    loading.value = false
  }
}

const saveProfile = async () => {
  try {
    if (editingProfile.value) {
      await profilesAPI.update(editingProfile.value.id, formData.value)
    } else {
      await profilesAPI.create(formData.value)
    }
    closeForm()
    loadProfiles()
  } catch (err) {
    error.value = 'Nie udało się zapisać: ' + err.message
  }
}

const editProfile = (profile) => {
  editingProfile.value = profile
  formData.value = { ...profile }
  showForm.value = true
}

const deleteProfile = async (id) => {
  if (confirm('Czy na pewno usunąć ten profil?')) {
    try {
      await profilesAPI.delete(id)
      loadProfiles()
    } catch (err) {
      error.value = 'Nie udało się usunąć: ' + err.message
    }
  }
}

const closeForm = () => {
  showForm.value = false
  editingProfile.value = null
  formData.value = {
    name: '',
    manufacturer: '',
    type: '',
    material: '',
    color: '',
    price_per_meter: 0
  }
}

onMounted(() => {
  loadProfiles()
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

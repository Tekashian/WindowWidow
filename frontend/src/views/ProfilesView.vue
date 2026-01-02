<template>
  <div class="profiles-view">
    <div class="header">
      <h1>Profile Okienne</h1>
      <button @click="showForm = true" class="btn btn-primary">+ Dodaj profil</button>
    </div>

    <SearchFilterBar 
      v-model="searchQuery"
      @update:modelValue="handleSearch"
      placeholder="Szukaj profili po nazwie, producencie..."
    />

    <LoadingSpinner v-if="loading" size="large" message="Ładowanie profili..." />

    <table v-if="!loading && profiles.length > 0" class="table">
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

    <PaginationControls
      v-if="!loading && pagination.last_page > 1"
      :pagination-data="pagination"
      @page-change="handlePageChange"
      @per-page-change="handlePerPageChange"
    />

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
import { useToast } from '@/composables/useToast'
import { useConfirm } from '@/composables/useConfirm'
import SearchFilterBar from '@/components/SearchFilterBar.vue'
import PaginationControls from '@/components/PaginationControls.vue'
import LoadingSpinner from '@/components/LoadingSpinner.vue'
import { profilesAPI } from '../services/api'

const { success, error: showError } = useToast()
const { confirm } = useConfirm()

const profiles = ref([])
const loading = ref(false)
const showForm = ref(false)
const editingProfile = ref(null)

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
  from: 0,
  to: 0
})
const searchQuery = ref('')

const formData = ref({
  name: '',
  manufacturer: '',
  type: '',
  material: '',
  color: '',
  price_per_meter: 0
})

const loadProfiles = async (page = 1) => {
  loading.value = true
  try {
    const params = {
      page,
      per_page: pagination.value.per_page,
      search: searchQuery.value || undefined
    }
    const response = await profilesAPI.getAll(params)
    
    if (response.data.data) {
      profiles.value = response.data.data
      pagination.value = {
        current_page: response.data.current_page,
        last_page: response.data.last_page,
        per_page: response.data.per_page,
        total: response.data.total,
        from: response.data.from,
        to: response.data.to
      }
    } else {
      profiles.value = response.data
    }
  } catch (err) {
    showError('Nie udało się załadować profili: ' + err.message)
  } finally {
    loading.value = false
  }
}

const handleSearch = () => {
  loadProfiles(1)
}

const handlePageChange = (page) => {
  loadProfiles(page)
}

const handlePerPageChange = (perPage) => {
  pagination.value.per_page = perPage
  loadProfiles(1)
}

const saveProfile = async () => {
  loading.value = true
  try {
    if (editingProfile.value) {
      await profilesAPI.update(editingProfile.value.id, formData.value)
      success('Profil został zaktualizowany')
    } else {
      await profilesAPI.create(formData.value)
      success('Profil został dodany')
    }
    closeForm()
    loadProfiles(pagination.value.current_page)
  } catch (err) {
    showError('Nie udało się zapisać: ' + err.message)
  } finally {
    loading.value = false
  }
}

const editProfile = (profile) => {
  editingProfile.value = profile
  formData.value = { ...profile }
  showForm.value = true
}

const deleteProfile = async (id) => {
  try {
    const confirmed = await confirm({
      title: 'Usunąć profil?',
      message: 'Czy na pewno chcesz usunąć ten profil?',
      confirmText: 'Usuń',
      cancelText: 'Anuluj',
      type: 'danger'
    })
    
    if (!confirmed) return
    
    loading.value = true
    await profilesAPI.delete(id)
    success('Profil został usunięty')
    loadProfiles(pagination.value.current_page)
  } catch (err) {
    if (err !== false) {
      showError('Nie udało się usunąć: ' + err.message)
    }
  } finally {
    loading.value = false
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

// Example: Complete integration of Must Have features in a view

import { ref, onMounted } from 'vue'
import { useToast } from '@/composables/useToast'
import { useConfirm } from '@/composables/useConfirm'
import api from '@/services/api'

// Components imports
// import SearchFilterBar from '@/components/SearchFilterBar.vue'
// import PaginationControls from '@/components/PaginationControls.vue'
// import LoadingSpinner from '@/components/LoadingSpinner.vue'

export default {
  name: 'ExampleView',
  setup() {
    const { success, error: showError } = useToast()
    const { confirm } = useConfirm()
    
    // State
    const items = ref([])
    const loading = ref(false)
    const pagination = ref({
      current_page: 1,
      last_page: 1,
      per_page: 15,
      total: 0,
      from: 0,
      to: 0
    })
    
    // Filters
    const searchQuery = ref('')
    const statusFilter = ref('')
    const sortBy = ref('created_at')
    const sortOrder = ref('desc')
    
    // Methods
    const fetchItems = async (page = 1) => {
      loading.value = true
      try {
        const params = {
          page,
          per_page: pagination.value.per_page,
          search: searchQuery.value || undefined,
          status: statusFilter.value || undefined,
          sort_by: sortBy.value,
          sort_order: sortOrder.value
        }
        
        const response = await api.get('/windows', { params })
        
        // Handle paginated response
        if (response.data.data) {
          items.value = response.data.data
          pagination.value = {
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            per_page: response.data.per_page,
            total: response.data.total,
            from: response.data.from,
            to: response.data.to
          }
        } else {
          items.value = response.data
        }
        
        success('Dane załadowane pomyślnie')
      } catch (err) {
        showError('Błąd podczas ładowania danych: ' + err.message)
      } finally {
        loading.value = false
      }
    }
    
    const handleSearch = (value) => {
      searchQuery.value = value
      fetchItems(1) // Reset to page 1 on new search
    }
    
    const handlePageChange = (page) => {
      fetchItems(page)
    }
    
    const handlePerPageChange = (perPage) => {
      pagination.value.per_page = perPage
      fetchItems(1)
    }
    
    const handleDelete = async (id) => {
      try {
        const confirmed = await confirm({
          title: 'Potwierdzenie usunięcia',
          message: 'Czy na pewno chcesz usunąć ten element? Tej operacji nie można cofnąć.',
          confirmText: 'Usuń',
          cancelText: 'Anuluj',
          type: 'danger'
        })
        
        if (!confirmed) return
        
        loading.value = true
        await api.delete(`/windows/${id}`)
        success('Element został usunięty')
        fetchItems(pagination.value.current_page)
      } catch (err) {
        if (err !== false) { // User didn't cancel
          showError('Błąd podczas usuwania: ' + err.message)
        }
      } finally {
        loading.value = false
      }
    }
    
    const handleSave = async (data) => {
      loading.value = true
      try {
        if (data.id) {
          await api.put(`/windows/${data.id}`, data)
          success('Zmiany zostały zapisane')
        } else {
          await api.post('/windows', data)
          success('Element został dodany')
        }
        fetchItems(pagination.value.current_page)
      } catch (err) {
        showError('Błąd podczas zapisywania: ' + err.message)
      } finally {
        loading.value = false
      }
    }
    
    onMounted(() => {
      fetchItems()
    })
    
    return {
      items,
      loading,
      pagination,
      searchQuery,
      statusFilter,
      sortBy,
      sortOrder,
      handleSearch,
      handlePageChange,
      handlePerPageChange,
      handleDelete,
      handleSave,
      fetchItems
    }
  }
}

// TEMPLATE USAGE:
// <template>
//   <div class="view">
//     <SearchFilterBar 
//       v-model="searchQuery"
//       @update:modelValue="handleSearch"
//       placeholder="Szukaj okien..."
//     >
//       <template #filters>
//         <select v-model="statusFilter" @change="fetchItems(1)">
//           <option value="">Wszystkie statusy</option>
//           <option value="active">Aktywne</option>
//           <option value="inactive">Nieaktywne</option>
//         </select>
//       </template>
//       
//       <template #actions>
//         <button @click="showForm = true" class="btn btn-primary">
//           + Dodaj nowy
//         </button>
//       </template>
//     </SearchFilterBar>
//     
//     <LoadingSpinner v-if="loading" size="large" message="Ładowanie danych..." />
//     
//     <div v-else class="items-grid">
//       <!-- Your items here -->
//     </div>
//     
//     <PaginationControls
//       :pagination-data="pagination"
//       @page-change="handlePageChange"
//       @per-page-change="handlePerPageChange"
//     />
//   </div>
// </template>

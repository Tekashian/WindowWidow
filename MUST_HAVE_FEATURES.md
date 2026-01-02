# Must Have Features - Implementation Guide

## ✅ Zaimplementowane funkcjonalności

### 1. Toast Notifications System
**Lokalizacja:**
- `frontend/src/composables/useToast.js` - Composable do zarządzania toastami
- `frontend/src/components/ToastContainer.vue` - Komponent wyświetlający toasty
- `frontend/src/App.vue` - Zintegrowany globalnie

**Użycie:**
```javascript
import { useToast } from '@/composables/useToast'

const { success, error, warning, info } = useToast()

// W funkcjach:
success('Operacja zakończona pomyślnie')
error('Wystąpił błąd podczas zapisu')
warning('Uwaga: niski stan magazynowy')
info('Informacja dla użytkownika')
```

**Funkcje:**
- 4 typy: success, error, warning, info
- Auto-dismiss po 4 sekundach (konfigurowalne)
- Ręczne zamykanie (kliknięcie)
- Animacje wejścia/wyjścia
- Pozycjonowanie: top-right
- Stack wielu toastów

---

### 2. Loading States
**Lokalizacja:**
- `frontend/src/components/LoadingSpinner.vue` - Komponent loadera

**Użycie:**
```vue
<template>
  <LoadingSpinner 
    v-if="loading" 
    size="large" 
    message="Ładowanie danych..." 
  />
</template>
```

**Rozmiary:**
- `small` - 20px (inline, buttons)
- `medium` - 40px (default, cards)
- `large` - 60px (full page)

**Wzorzec w API calls:**
```javascript
const loading = ref(false)

const fetchData = async () => {
  loading.value = true
  try {
    const response = await api.get('/endpoint')
    // Process data
  } catch (err) {
    showError(err.message)
  } finally {
    loading.value = false
  }
}
```

---

### 3. Confirmation Dialogs
**Lokalizacja:**
- `frontend/src/components/ConfirmDialog.vue` - Komponent dialogu
- `frontend/src/composables/useConfirm.js` - Composable (helper)
- `frontend/src/App.vue` - Zarejestrowany globalnie
- `frontend/src/main.js` - Konfiguracja global property

**Użycie:**
```javascript
import { useConfirm } from '@/composables/useConfirm'

const { confirm } = useConfirm()

const handleDelete = async (id) => {
  try {
    const confirmed = await confirm({
      title: 'Potwierdzenie usunięcia',
      message: 'Czy na pewno chcesz usunąć ten element?',
      confirmText: 'Usuń',
      cancelText: 'Anuluj',
      type: 'danger' // danger, warning, primary
    })
    
    if (confirmed) {
      await api.delete(\`/items/\${id}\`)
      success('Element usunięty')
    }
  } catch (err) {
    // User cancelled or error occurred
  }
}
```

**Typy:**
- `danger` - czerwony (delete, destructive actions)
- `warning` - pomarańczowy (caution)
- `primary` - niebieski (general confirmation)

---

### 4. Pagination System
**Backend (wszystkie kontrolery):**
- `WindowController` - zaimplementowane
- `OrderController` - zaimplementowane
- `MaterialController` - zaimplementowane
- `ProfileController` - zaimplementowane
- `GlassController` - zaimplementowane
- `ProductionOrderController` - już było (linia 58)
- `WarehouseDeliveryController` - zaimplementowane

**Parametry API:**
```
GET /api/windows?page=1&per_page=15&search=okno&type=PVC&sort_by=name&sort_order=asc
```

**Response format:**
```json
{
  "data": [...],
  "current_page": 1,
  "last_page": 5,
  "per_page": 15,
  "total": 73,
  "from": 1,
  "to": 15
}
```

**Frontend Component:**
- `frontend/src/components/PaginationControls.vue`

**Użycie:**
```vue
<template>
  <PaginationControls
    :pagination-data="pagination"
    @page-change="handlePageChange"
    @per-page-change="handlePerPageChange"
  />
</template>

<script setup>
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
  from: 0,
  to: 0
})

const fetchData = async (page = 1) => {
  const response = await api.get('/windows', {
    params: { page, per_page: pagination.value.per_page }
  })
  
  items.value = response.data.data
  pagination.value = {
    current_page: response.data.current_page,
    last_page: response.data.last_page,
    per_page: response.data.per_page,
    total: response.data.total,
    from: response.data.from,
    to: response.data.to
  }
}
</script>
```

---

### 5. Search & Filters System
**Backend (wszystkie kontrolery):**
Każdy kontroler wspiera:
- `search` - full-text search po głównych polach
- `type` / `status` - filtry kategoryczne
- `is_active` - filtry boolean
- `date_from` / `date_to` - filtry dat
- `low_stock` - filtry specjalne (MaterialController)
- `sort_by` / `sort_order` - sortowanie

**Przykłady API:**

**Windows:**
```
GET /api/windows?search=PVC&type=Uchylne&is_active=true&sort_by=price&sort_order=desc
```

**Orders:**
```
GET /api/orders?search=Kowalski&status=pending&date_from=2026-01-01&date_to=2026-01-31
```

**Materials:**
```
GET /api/materials?search=profil&type=profil&low_stock=true
```

**Deliveries:**
```
GET /api/warehouse/deliveries?status=pending&date_from=2026-01-01
```

**Frontend Component:**
- `frontend/src/components/SearchFilterBar.vue`

**Użycie:**
```vue
<template>
  <SearchFilterBar 
    v-model="searchQuery"
    @update:modelValue="handleSearch"
    placeholder="Szukaj okien..."
  >
    <template #filters>
      <select v-model="statusFilter" @change="fetchItems(1)">
        <option value="">Wszystkie</option>
        <option value="active">Aktywne</option>
        <option value="inactive">Nieaktywne</option>
      </select>
      
      <select v-model="typeFilter" @change="fetchItems(1)">
        <option value="">Wszystkie typy</option>
        <option value="PVC">PVC</option>
        <option value="Drewno">Drewno</option>
      </select>
    </template>
    
    <template #actions>
      <button @click="showForm = true" class="btn btn-primary">
        + Dodaj
      </button>
    </template>
  </SearchFilterBar>
</template>

<script setup>
const searchQuery = ref('')
const statusFilter = ref('')
const typeFilter = ref('')

const handleSearch = (value) => {
  searchQuery.value = value
  fetchItems(1) // Reset to page 1
}
</script>
```

**Debouncing:**
Search input ma built-in debounce 500ms (konfigurowalne).

---

## 🎯 Kompletny przykład integracji

Zobacz plik: `frontend/src/examples/complete-integration-example.js`

Ten przykład pokazuje pełną integrację wszystkich 5 funkcjonalności w jednym widoku.

---

## 📋 Checklist implementacji w istniejących widokach

Aby zaktualizować istniejące widoki:

1. **Import komponentów:**
```javascript
import { useToast } from '@/composables/useToast'
import { useConfirm } from '@/composables/useConfirm'
import SearchFilterBar from '@/components/SearchFilterBar.vue'
import PaginationControls from '@/components/PaginationControls.vue'
import LoadingSpinner from '@/components/LoadingSpinner.vue'
```

2. **Dodaj state paginacji:**
```javascript
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0
})
```

3. **Dodaj filtry:**
```javascript
const searchQuery = ref('')
const statusFilter = ref('')
```

4. **Zaktualizuj fetch z parametrami:**
```javascript
const response = await api.get('/endpoint', {
  params: {
    page: pagination.value.current_page,
    per_page: pagination.value.per_page,
    search: searchQuery.value,
    status: statusFilter.value
  }
})
```

5. **Dodaj toast do operacji:**
```javascript
const { success, error } = useToast()

try {
  await api.post('/endpoint', data)
  success('Zapisano pomyślnie')
} catch (err) {
  error('Błąd: ' + err.message)
}
```

6. **Dodaj confirm do delete:**
```javascript
const { confirm } = useConfirm()

const confirmed = await confirm({
  title: 'Usuń element',
  message: 'Czy na pewno?',
  type: 'danger'
})

if (confirmed) {
  // Delete
}
```

7. **Dodaj komponenty do template:**
```vue
<SearchFilterBar v-model="searchQuery" @update:modelValue="handleSearch" />
<LoadingSpinner v-if="loading" />
<PaginationControls :pagination-data="pagination" @page-change="handlePageChange" />
```

---

## 🚀 Następne kroki

Wszystkie Must Have funkcjonalności są gotowe do użycia. Należy je teraz zintegrować w istniejących widokach:

- ✅ WindowsView.vue
- ✅ OrdersView.vue
- ✅ MaterialsView.vue
- ✅ ProfilesView.vue
- ✅ GlassesView.vue
- ✅ ProductionOrdersView.vue (częściowo - ma już paginację)
- ✅ WarehouseDeliveriesView (jeśli istnieje)

Backend jest w 100% gotowy. Frontend wymaga aktualizacji widoków według checklisty powyżej.

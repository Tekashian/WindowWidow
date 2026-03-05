# WindowWidow — AI Agent: Comprehensive System Instructions

## 1. ROLA AGENTA

Jesteś ekspertem full-stack od tego konkretnego systemu. Twoja praca to: **analizować istniejący kod → rozumieć kontekst → implementować zadanie zgodnie z ustalonymi wzorcami projektu**. Nie wymyślasz nowej architektury — rozszerzasz istniejącą. Zawsze czytasz powiązany kod przed pisaniem nowego.

---

## 2. SYSTEM: WindowWidow

Profesjonalny system ERP do zarządzania produkcją okien. Trzy niezależne panele:

| Panel | Ścieżka frontend | Rola | Odpowiedzialność |
|---|---|---|---|
| **Admin** | `/admin/*` | `admin` | Katalog produktów, zamówienia klientów, analityka |
| **Produkcja** | `/production/*` | `production`, `admin` | Zlecenia produkcyjne, partie, problemy |
| **Magazyn** | `/warehouse/*` | `warehouse`, `admin` | Dostawy, stany magazynowe materiałów |

---

## 3. STOS TECHNOLOGICZNY

### Backend
- **PHP 8.1** / **Laravel 10.x** — `backend/`
- **Laravel Sanctum** — autentykacja tokenem Bearer
- **MySQL** — baza danych
- **Wzorzec**: Controller → Service → Model (Service tylko gdy logika biznesowa jest złożona)
- **PSR-12** — standard kodowania PHP

### Frontend
- **Vue.js 3** z Composition API (`<script setup>`) — `frontend/src/`
- **Vite** — bundler
- **Pinia** — zarządzanie stanem (`frontend/src/stores/`)
- **Vue Router 4** — routing z guard'ami roli
- **Vanilla CSS** — bez Tailwind/Bootstrap (własne klasy komponentów)
- **JavaScript** (nie TypeScript mimo notatki w docs)

---

## 4. STRUKTURA KATALOGÓW

```
backend/
├── app/
│   ├── Http/Controllers/Api/     # AuthController, DashboardController, WindowController,
│   │                             # ProfileController, GlassController, OrderController,
│   │                             # MaterialController, ProductionOrderController, ImageUploadController
│   ├── Http/Controllers/         # ProductionOrderController (nowy), ProductionBatchController,
│   │                             # ProductionIssueController, WarehouseDeliveryController,
│   │                             # NotificationController
│   ├── Http/Middleware/          # role middleware
│   ├── Models/                   # Glass, Material, Notification, Order, OrderItem,
│   │                             # ProductionBatch, ProductionIssue, ProductionMaterial,
│   │                             # ProductionOrder, ProductionOrderItem, ProductionTimeline,
│   │                             # Profile, StockMovement, User, WarehouseDelivery, Window
│   ├── Services/                 # ProductionOrderService, DashboardService, NotificationService
│   ├── Events/                   # LowStockAlert, ProductionOrderCompleted, ProductionStarted
│   ├── Listeners/                # NotifyLowStock, NotifyProductionOrderCompleted,
│   │                             # NotifyWarehouseAboutDelivery
│   └── Policies/
├── database/migrations/          # Pełna historia migracji
└── routes/api.php                # Wszystkie REST endpointy

frontend/src/
├── views/
│   ├── production/               # ProductionDashboard, ProductionOrdersList,
│   │                             # ProductionOrderDetails, ProductionOrderForm, ProductionIssues
│   ├── warehouse/                # WarehouseDashboard, Materials, Deliveries
│   ├── admin/                    # AdminDashboard, Reports
│   └── [root views]              # HomeView, LoginView, WindowsView, ProfilesView,
│                                 # GlassesView, OrdersView, MaterialsView
├── stores/                       # auth.js, productionStore.js, warehouseStore.js,
│                                 # notificationStore.js, index.js
├── services/                     # api.js (axios instance), productionApi.js,
│                                 # warehouseApi.js, notificationApi.js
├── components/                   # ConfirmDialog.vue, LoadingSpinner.vue,
│                                 # NotificationCenter.vue, PaginationControls.vue,
│                                 # SearchFilterBar.vue, ToastContainer.vue
├── composables/                  # useToast.js, useConfirm.js
└── router/index.js               # Vue Router z guardami roli
```

---

## 5. BAZA DANYCH — KLUCZOWE MODELE I RELACJE

```
users                     (id, name, email, password, role[admin|production|warehouse])
├── windows               (id, name, sku, profile_id, glass_id, width, height, price,
│                          stock_quantity, min_stock, image_url, is_active)
│   ├── profiles          (id, name, material, color, description, price_per_meter)
│   └── glasses           (id, name, type, thickness, u_value, price_per_m2)
├── orders                (id, order_number, customer_name, status, total_price, ...)
│   └── order_items       (id, order_id, window_id, quantity, unit_price)
├── materials             (id, name, unit, stock_quantity, min_stock_level, price_per_unit)
│   └── stock_movements   (id, material_id, type[in|out], quantity, reason, created_by)
├── production_orders     (id, order_number[PRD-2026-XXXX], source_type, source_id,
│   │                      customer_name, product_type, quantity, status, priority,
│   │                      confirmed_by_production, is_delayed, started_at,
│   │                      estimated_completion_at, actual_completion_at, ...)
│   ├── production_order_items (id, production_order_id, window_id, quantity, status)
│   ├── production_timeline    (id, production_order_id, status, notes, created_by)
│   ├── production_batches     (id, production_order_id, batch_number, quantity,
│   │                           status[in_production|quality_check|ready|shipped|rejected])
│   ├── production_issues      (id, production_order_id, title, description, severity,
│   │                           status[open|in_progress|resolved], resolved_at)
│   └── production_materials   (id, production_order_id, material_id, quantity_required,
│                               quantity_used)
└── warehouse_deliveries  (id, production_order_id, batch_id, status[pending|shipped|
                           received|rejected], rejection_reason, shipped_at, received_at)

notifications             (id, user_id, type, title, message, data[JSON], read_at)
```

---

## 6. RBAC — ROLE I UPRAWNIENIA

| Operacja | admin | production | warehouse |
|---|:---:|:---:|:---:|
| CRUD Windows/Profiles/Glasses/Orders | ✅ | ❌ | ❌ |
| Odczyt katalog & zamówień | ✅ | ✅ | ✅ |
| CRUD Materiały + stany mag. | ✅ | ❌ | ✅ |
| Zlecenia produkcyjne (CRUD + akcje) | ✅ | ✅ | ❌ |
| Dostawy magazynowe (akcje) | ✅ | ❌ | ✅ |
| Upload obrazów | ✅ | ❌ | ❌ |
| Dashboard | ✅ | ✅ | ✅ |

**Middleware backend**: `role:admin`, `role:admin,production`, `role:admin,warehouse`  
**Guard frontend**: `meta: { requiresRole: ['admin', 'production'] }` w router/index.js

---

## 7. STATE MACHINE — CYKL ZLECENIA PRODUKCYJNEGO

```
[POST /production/orders]
        │
        ▼
    PENDING ──────────────────────────────────────────────────────────┐
        │                                                              │
        │ POST .../start  lub  POST .../update-status                  │
        │                                                              │
        ▼                                                              │
  MATERIALS_CHECK → MATERIALS_RESERVED → IN_PROGRESS ←──────────┐   │
                                              │                   │   │
                              ┌───────────────┼───────────────┐   │   │
                              ▼               ▼               ▼   │   │
                       create-batch    report-issue      report-delay  │
                              │         (critical→      │               │
                              │          ON_HOLD)        │               │
                              ▼               │          ▼               │
                      QUALITY_CHECK       ON_HOLD──────►IN_PROGRESS      │
                              │                                          │
                              ▼                                          │
                          COMPLETED                                      │
                              │                                          │
                    POST .../ship-to-warehouse                           │
                              │                                          │
                              ▼                                          │
                   SHIPPED_TO_WAREHOUSE                                  │
                                                                         │
                         CANCELLED ◄─────────────────────────────────────┘
                    (admin może anulować z większości stanów)
```

**Ważne**: `POST .../confirm` NIE zmienia statusu — ustawia `confirmed_by_production=true` i notyfikuje admina.

---

## 8. KLUCZOWE ENDPOINTY API

**Base URL**: `http://localhost:8000/api`  
**Auth**: `Authorization: Bearer {token}`

```
POST   /login                                    # publiczny
GET    /me                                       # profil zalogowanego
GET    /dashboard                                # statystyki (wszystkie role)

# Katalog
GET|POST        /windows
GET|PUT|DELETE  /windows/{id}
POST            /windows/{id}/update-stock

GET|POST        /profiles
GET|POST        /glasses
GET|POST|PUT    /orders | /orders/{id}
POST            /orders/{id}/update-status

# Materiały
GET|POST|PUT|DELETE  /materials/{id}
POST                 /materials/{id}/add-stock
POST                 /materials/{id}/remove-stock
GET                  /materials/{id}/movements
GET                  /low-stock

# Produkcja
GET    /production/products                      # lista okien do wyboru
GET    /production/orders                        # lista zleceń
POST   /production/orders                        # nowe zlecenie
GET    /production/orders/{id}
PUT    /production/orders/{id}
POST   /production/orders/{id}/confirm           # potwierdź (nie zmienia statusu)
POST   /production/orders/{id}/start             # pending → in_progress
POST   /production/orders/{id}/update-status     # zmiana statusu
POST   /production/orders/{id}/update-progress   # postęp + opcjonalna zmiana statusu
POST   /production/orders/{id}/report-issue      # zgłoś problem
POST   /production/orders/{id}/report-delay      # zgłoś opóźnienie
POST   /production/orders/{id}/create-batch      # utwórz partię
POST   /production/orders/{id}/ship-to-warehouse # wyślij do magazynu
GET    /production/orders/statistics
GET    /production/batches | /production/batches/{id}
POST   /production/batches/{id}/update-status
GET    /production/issues | /production/issues/{id}
POST   /production/issues/{id}/resolve

# Magazyn
GET    /warehouse/deliveries
GET    /warehouse/deliveries/{id}
POST   /warehouse/deliveries/{id}/ship
POST   /warehouse/deliveries/{id}/receive
POST   /warehouse/deliveries/{id}/reject

# Powiadomienia
GET    /notifications
GET    /notifications/unread-count
POST   /notifications/mark-all-read
POST   /notifications/{id}/mark-read
```

---

## 9. WZORCE IMPLEMENTACYJNE — OBOWIĄZKOWE

### 9.1 Backend — Controller (przykład referencyjny)
```php
// app/Http/Controllers/Api/ExampleController.php
namespace App\Http\Controllers\Api;

use App\Models\Example;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ExampleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Example::query();
        // filtrowanie przez $request->get('search')
        // paginacja: ->paginate(15)
        return response()->json($query->paginate(15));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            // ...
        ]);
        $item = Example::create($validated);
        return response()->json($item, 201);
    }

    public function update(Request $request, Example $example): JsonResponse
    {
        $validated = $request->validate([/* ... */]);
        $example->update($validated);
        return response()->json($example);
    }

    public function destroy(Example $example): JsonResponse
    {
        $example->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
```

### 9.2 Backend — Service (złożona logika biznesowa)
```php
// app/Services/ExampleService.php
// Używaj DB::beginTransaction() / DB::commit() / DB::rollBack()
// Rzucaj wyjątki Exception z opisem po polsku/angielsku
// Loguj błędy przez \Log::warning() lub \Log::error()
```

### 9.3 Frontend — Vue Component (`<script setup>`)
```vue
<script setup>
import { ref, computed, onMounted } from 'vue'
import { useToast } from '@/composables/useToast'
import { useConfirm } from '@/composables/useConfirm'
import api from '@/services/api'

const { success, error } = useToast()
const { confirm } = useConfirm()

const items = ref([])
const loading = ref(false)

const fetchItems = async () => {
  loading.value = true
  try {
    const response = await api.get('/endpoint')
    items.value = response.data.data ?? response.data
  } catch (err) {
    error(err.response?.data?.message ?? 'Wystąpił błąd')
  } finally {
    loading.value = false
  }
}

const handleDelete = async (id) => {
  const confirmed = await confirm({
    title: 'Potwierdzenie usunięcia',
    message: 'Czy na pewno chcesz usunąć ten element?',
    confirmText: 'Usuń',
    type: 'danger'
  })
  if (!confirmed) return
  try {
    await api.delete(`/endpoint/${id}`)
    success('Element usunięty pomyślnie')
    await fetchItems()
  } catch (err) {
    error(err.response?.data?.message ?? 'Błąd podczas usuwania')
  }
}

onMounted(fetchItems)
</script>

<template>
  <div>
    <LoadingSpinner v-if="loading" size="large" message="Ładowanie..." />
    <!-- content -->
  </div>
</template>
```

### 9.4 Frontend — Pinia Store
```javascript
// stores/exampleStore.js
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useExampleStore = defineStore('example', () => {
  const items = ref([])
  const loading = ref(false)
  const error = ref(null)

  const fetchItems = async () => {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.get('/endpoint')
      items.value = data.data ?? data
    } catch (err) {
      error.value = err.response?.data?.message ?? 'Błąd'
    } finally {
      loading.value = false
    }
  }

  return { items, loading, error, fetchItems }
})
```

### 9.5 Globalne komponenty (dostępne wszędzie bez importu)
- `<LoadingSpinner size="small|medium|large" message="..." />`
- `<ConfirmDialog />` — używaj przez composable `useConfirm()`
- `<ToastContainer />` — używaj przez composable `useToast()`
- `<PaginationControls />`
- `<SearchFilterBar />`
- `<NotificationCenter />`

---

## 10. DOMENOWA WIEDZA BIZNESOWA

### Produkty
- **Okno** (`Window`): ma `profile_id`, `glass_id`, wymiary (mm), cenę, SKU, stany magazynowe
- **Profil** (`Profile`): rama okna — material (PVC/aluminium/drewno), kolor, cena/mb
- **Szyba** (`Glass`): typ (jednokomorowa/dwukomorowa/trzyszybowa), grubość, współczynnik U, cena/m²

### Produkcja
- Zlecenie produkcyjne (`ProductionOrder`) jest sercem systemu
- `order_number` format: `PRD-2026-XXXX` (auto-generowany)
- `confirmed_by_production` — pracownik produkcji potwierdza realność terminu ZANIM produkcja się zacznie
- `ProductionBatch` — fizyczna partia wyprodukowanych okien (jedno zlecenie może mieć wiele partii)
- `ProductionIssue` — problem na linii; `severity=critical` auto-wstrzymuje zlecenie
- `ProductionTimeline` — log każdej zmiany statusu

### Magazyn
- `Material` — surowce (profile w mb, szyby w m², uszczelki, okucia)
- `StockMovement` — każde `in/out` jest logowane z powodem i użytkownikiem
- `WarehouseDelivery` — odebranie gotowych okien z produkcji do magazynu

---

## 11. PROTOKÓŁ WYKONANIA TASKA

Każde zadanie wykonuj w tej kolejności:

```
1. ZROZUM TASK
   - Co dokładnie ma być zrobione?
   - Czego NIE ma w systemie a powinno być?
   - Jakie role mają dostęp?

2. ZBADAJ ISTNIEJĄCY KOD
   - Znajdź najbardziej podobny istniejący kontroler/komponent/store
   - Sprawdź migracje — czy tabela już istnieje?
   - Sprawdź routes/api.php — czy endpoint już jest?
   - Sprawdź router/index.js — czy trasa vue już jest?

3. ZAPLANUJ ZMIANY
   - Backend: migracja (jeśli nowa tabela) → Model → Service (jeśli złożone) → Controller → Route
   - Frontend: Store → Service/Api → View → Router

4. IMPLEMENTUJ
   - Trzymaj się wzorców z sekcji 9
   - Używaj istniejących komponentów globalnych (sekcja 9.5)
   - Nazewnictwo: camelCase (JS), snake_case (PHP), PascalCase (klasy/komponenty)
   - Komentarze po polsku (zgodnie z istniejącym kodem)

5. WALIDUJ
   - Czy `$fillable` w modelu zawiera nowe pola?
   - Czy endpoint jest zabezpieczony właściwym middleware roli?
   - Czy frontend obsługuje loading i błędy?
   - Czy toast jest wyświetlony po sukcesie i błędzie?
   - Czy paginacja jest obsługiwana (jeśli lista)?
```

---

## 12. ZASADY BEZWZGLĘDNE

1. **Nie zmieniaj architektury** — rozszerzaj istniejące wzorce, nie wymyślaj nowych
2. **Zawsze sprawdzaj istniejący kod** przed napisaniem nowego — szukaj podobnych implementacji
3. **Sanctum token** jest wymagany do wszystkich endpointów oprócz `/login` i `/health`
4. **Transakcje DB** przy operacjach na wielu tabelach — `DB::beginTransaction()`
5. **Error handling** — try/catch w serwisach, walidacja w kontrolerach przez `$request->validate()`
6. **Toast notifications** — zawsze po sukcesie i błędzie w akcjach użytkownika
7. **Loading state** — zawsze przy asynchronicznych operacjach w Vue
8. **Role check** zarówno na backendzie (middleware) jak i frontendzie (router meta + v-if)
9. **Paginacja** dla list — backend `->paginate(15)`, frontend obsługuje `data.current_page` itd.
10. **Język**: komunikaty dla użytkownika — **po polsku**, kod/zmienne/komentarze techniczne — do wyboru

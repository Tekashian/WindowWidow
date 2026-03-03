# Frontend — Przepływ Danych Vue 3 + Pinia

## Diagram

```plantuml
@startuml Frontend_Data_Flow
!pragma layout smetana

skinparam backgroundColor #FAFAFA
skinparam defaultFontName Arial
skinparam defaultFontSize 11

skinparam package {
  BackgroundColor #F5F5F5
  BorderColor #78909C
  BorderThickness 2
  FontStyle bold
  FontSize 12
}

skinparam component {
  RoundCorner 8
  BorderThickness 1.5
  FontSize 10
}

skinparam arrow {
  Color #455A64
  Thickness 1.5
  FontSize 9
  FontColor #546E7A
}

skinparam note {
  BackgroundColor #FFFDE7
  BorderColor #FBC02D
  FontSize 9
}

' ── VIEWS ──────────────────────────────────────────
package "Views  (lazy-loaded per role)" {
  component "LoginView\n<<public>>" as LV #FAFAFA

  component "HomeView\n<<dashboard>>" as HV #E3F2FD

  component "Admin Panel\n/admin/*\nAdminDashboard | WindowsManagement | WindowForm" as AV #EDE7F6

  component "Production Panel\n/production/*\nDashboard | OrdersList | OrderDetails\nOrderForm | Issues" as PV #FFEBEE

  component "Warehouse Panel\n/warehouse/*\nDashboard | Materials" as WV #E8F5E9

  component "Shared Views\n/materials | /windows | /profiles\n/glasses | /orders  (read-only for all)" as SV #F5F5F5
}

' ── STORES ─────────────────────────────────────────
package "Pinia Stores  (reactive global state)" {
  component "authStore\n─────────────\nstate: user, token, tokenExpiry\ncomputed: isAuthenticated\nactions: login(), logout(), fetchUser()" as AUTH #E8EAF6

  component "stores/index.js  (main store)\n─────────────\nstate: materials[], lowStock[]\n       orders[], windows[]\nactions: fetchMaterials(), fetchOrders()..." as MAIN #E3F2FD

  component "productionStore\n─────────────\nstate: orders[], statistics, issues[], batches[]\nactions: fetchOrders(), startProduction(),\n         createBatch(), shipToWarehouse()..." as PROD #FFEBEE

  component "warehouseStore\n─────────────\nstate: deliveries[], statistics{\n  total_deliveries, pending, in_transit,\n  delayed, delivered, rejected}\nactions: fetchDeliveries(), shipDelivery(),\n         receiveDelivery(), rejectDelivery()..." as WARE #E8F5E9

  component "notificationStore\n─────────────\nstate: notifications[], unreadCount\nactions: fetchAll(), markRead(),\n         markAllRead(), deleteRead()..." as NOTIF #F3E5F5
}

' ── HTTP SERVICES ───────────────────────────────────
package "HTTP Services  (4 Axios clients)" {
  component "services/api.js\n─────────────\nAxios instance, baseURL = '/api'\nrequest interceptor: inject Bearer token\nresponse interceptor:\n  401 → localStorage.clear + redirect /login\n  403 / 404 / 5xx → console.error\nused by: authStore, main store" as API #FFF8E1

  component "services/productionApi.js\n─────────────\nAPI_BASE = '/api'\nManual token from localStorage\nused by: productionStore" as PRODAPI #FFEBEE

  component "services/warehouseApi.js\n─────────────\nAPI_BASE = '/api'\nManual token from localStorage\nused by: warehouseStore" as WAPI #E8F5E9

  component "services/notificationApi.js\n─────────────\nAPI_URL = '/api'\nManual token from localStorage\nused by: notificationStore" as NAPI #F3E5F5
}

' ── ROUTER GUARD ────────────────────────────────────
package "Vue Router 4  — Navigation Guard" {
  component "router/index.js  beforeEach()\n─────────────────────────────────────────\n1. requiresAuth  +  !isAuthenticated  →  /login\n2. path == /login  +  isAuthenticated  →  /\n3. requiresRole  +  role not included  →  /" as GUARD #E0F2F1
}

' ── CONNECTIONS ─────────────────────────────────────
LV -down-> AUTH  : login() on submit

HV   -down-> MAIN  : fetchMaterials(), fetchOrders()
HV   -down-> NOTIF : fetchNotifications()
AV   -down-> MAIN  : fetchWindows()
PV   -down-> PROD  : fetchOrders(), updateStatus()\ncreateOrder(), shipToWarehouse()
WV   -down-> WARE  : fetchDeliveries(), receive()
SV   -down-> MAIN  : shared reads

AUTH  -down-> API    : authAPI.login/logout/me
MAIN  -down-> API    : materialsAPI, dashboardAPI
PROD  -down-> PRODAPI : all production calls
WARE  -down-> WAPI   : all warehouse calls
NOTIF -down-> NAPI   : all notification calls

GUARD ..> LV : guards
GUARD ..> HV : guards
GUARD ..> AV : guards (requiresRole: admin)
GUARD ..> PV : guards (requiresRole: production|admin)
GUARD ..> WV : guards (requiresRole: warehouse|admin)

note bottom of API
  response.data.data — Laravel paginacja:
  { data: [...], current_page, total, per_page }
  fetchOrders(): response.data.data || response.data
  fetchDeliveries(): response.data.data ?? response.data
end note
@enduml
```

## Pinia — authStore (kluczowy store)

```javascript
// stores/auth.js
const user = ref(null)
const token = ref(localStorage.getItem('token'))
const tokenExpiry = ref(localStorage.getItem('tokenExpiry'))

const isAuthenticated = computed(() => {
  if (!token.value || !tokenExpiry.value) return false
  return Date.now() < parseInt(tokenExpiry.value)  // 30 minut
})
```

**Kluczowe rzeczy**:
- Token sesji trwa **30 minut** (odnowienie wymaga ponownego logowania)
- `isAuthenticated` to `computed` — reaktywnie aktualizuje się
- Router guard używa `isAuthenticated` przy każdej nawigacji

---

## Pinia — warehouseStore (zaktualizowany)

```javascript
// stores/warehouseStore.js
statistics: {
  total_deliveries: 0,
  pending: 0,
  in_transit: 0,
  delayed: 0,
  delivered_today: 0,
  delivered: 0,   // ← dodane
  rejected: 0     // ← dodane
}
```

`fetchStatistics()` odpyta `GET /api/warehouse/deliveries/statistics` — backend
now returns all 7 fields (was missing `total_deliveries`, `delivered`, `rejected`).

---

## 4 klienty Axios (nie jeden!)

| Plik | Kto używa | Interceptor | Token |
|------|------------|-------------|-------|
| `services/api.js` | authStore, main store | request + response | interceptor |
| `services/productionApi.js` | productionStore | brak | ręcznie z localStorage |
| `services/warehouseApi.js` | warehouseStore | brak | ręcznie z localStorage |
| `services/notificationApi.js` | notificationStore | brak | ręcznie z localStorage |

Tylko `api.js` ma interceptor odpowiedzi (401 → redirect `/login`, 403 → log).
Pozostałe 3 klienty używają `getAuthHeaders()` / ręcznego `localStorage.getItem('token')`.

---

## Struktura katalogów frontend/src/

```
src/
├── App.vue                   ← root komponent, montuje <router-view>
├── main.js                   ← rejestruje Vue app + Pinia + Router
│
├── router/
│   └── index.js              ← trasy + beforeEach guard
│
├── stores/
│   ├── auth.js               ← logowanie, token, user
│   ├── index.js              ← materials, orders, windows, lowStock
│   ├── productionStore.js    ← zlecenia produkcji, statystyki, issues
│   ├── warehouseStore.js     ← dostawy magazynowe
│   └── notificationStore.js  ← powiadomienia
│
├── services/
│   ├── api.js                ← Axios + interceptor, bazowy klient
│   └── warehouseApi.js       ← Axios dla magazynu (osobny klient)
│
└── views/
    ├── LoginView.vue          ← formularz logowania
    ├── HomeView.vue           ← ogólny dashboard
    ├── MaterialsView.vue      ← podgląd materiałów (wszyscy)
    ├── WindowsView.vue        ← katalog okien
    ├── ProfilesView.vue       ← katalog profili
    ├── GlassesView.vue        ← katalog szyb
    ├── OrdersView.vue         ← zamówienia klientów
    ├── admin/
    │   ├── AdminDashboard.vue
    │   ├── WindowsManagement.vue
    │   └── WindowForm.vue
    ├── production/
    │   ├── ProductionDashboard.vue
    │   ├── ProductionOrdersList.vue
    │   ├── ProductionOrderDetails.vue
    │   ├── ProductionOrderForm.vue
    │   └── ProductionIssues.vue
    └── warehouse/
        ├── WarehouseDashboard.vue
        └── Materials.vue
```

---

## Lazy Loading widoków (Vue Router)

```javascript
// router/index.js — komponenty ładowane dopiero kiedy potrzebne
component: () => import('../views/production/ProductionOrdersList.vue')
```

→ Mniejszy bundle startowy  
→ Każdy panel (admin/produkcja/magazyn) ładuje się przy pierwszej wizycie

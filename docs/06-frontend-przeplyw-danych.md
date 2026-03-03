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

  component "Admin Panel\n/admin/*\nAdminDashboard | WindowsManagement" as AV #EDE7F6

  component "Production Panel\n/production/*\nDashboard | OrdersList | Details | Issues" as PV #FFEBEE

  component "Warehouse Panel\n/warehouse/*\nDashboard | Materials" as WV #E8F5E9

  component "Shared Views\n/materials | /windows | /profiles\n/glasses | /orders  (read-only for all)" as SV #F5F5F5
}

' ── STORES ─────────────────────────────────────────
package "Pinia Stores  (reactive global state)" {
  component "authStore\n─────────────\nstate: user, token, tokenExpiry\ncomputed: isAuthenticated\nactions: login(), logout(), fetchUser()" as AUTH #E8EAF6

  component "stores/index.js  (main store)\n─────────────\nstate: materials[], lowStock[]\n       orders[], windows[]\nactions: fetchMaterials(), fetchOrders()..." as MAIN #E3F2FD

  component "productionStore\n─────────────\nstate: orders[], statistics, issues[]\nactions: fetchOrders(), updateStatus()..." as PROD #FFEBEE

  component "warehouseStore\n─────────────\nstate: deliveries[], statistics\nactions: fetchDeliveries(), receive()..." as WARE #E8F5E9

  component "notificationStore\n─────────────\nstate: notifications[], unreadCount\nactions: fetchAll(), markRead()..." as NOTIF #F3E5F5
}

' ── HTTP SERVICES ───────────────────────────────────
package "HTTP Services" {
  component "services/api.js\n─────────────\nAxios instance\nbaseURL = '/api'\ninterceptor: inject Bearer token\nhandles 401 → logout" as API #FFF8E1

  component "services/warehouseApi.js\n─────────────\nDedicated warehouse client\nAPI_BASE = '/api'\nManual token from localStorage" as WAPI #FFF8E1
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
PV   -down-> PROD  : fetchOrders(), updateStatus()
WV   -down-> WARE  : fetchDeliveries(), receive()
SV   -down-> MAIN  : shared reads

AUTH -down-> API  : uses
MAIN -down-> API  : uses
PROD -down-> API  : uses
NOTIF -down-> API : uses
WARE -down-> WAPI : uses

GUARD ..> LV : guards
GUARD ..> HV : guards
GUARD ..> AV : guards (role: admin)
GUARD ..> PV : guards (role: production|admin)
GUARD ..> WV : guards (role: warehouse|admin)

note bottom of API
  response.data.data
  ── Każda lista jest paginowana przez Laravel:
  { data: [...], current_page, total, per_page }
  Dlatego NIE response.data ale response.data.data
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

## Pinia — stores/index.js (główny store danych)

```javascript
// stores/index.js
async function fetchMaterials() {
  const response = await api.get('/materials')
  materials.value = response.data.data ?? response.data  // ← unwrap paginacji!
}
```

> ⚠️ **Ważne**: Laravel zwraca paginację `{ data: [...], current_page: 1, total: 50 }`.  
> Dlatego `response.data.data` — pierwsze `.data` to Axios, drugie `.data` to Laravel pagination.

---

## Jak działa żądanie HTTP (krok po kroku)

```
1. Komponent wywołuje store.fetchMaterials()
2. Store wywołuje api.get('/materials')
3. Axios interceptor dodaje nagłówek:
   Authorization: Bearer <token z localStorage>
4. Vite proxy przekierowuje /api/materials → http://localhost:8000/api/materials
5. Laravel sprawdza token (Sanctum middleware)
6. Kontroler odpowiada JSON z paginacją
7. Store zapisuje response.data.data do state
8. Komponent reaktywnie re-renderuje się przez Vue reactivity
```

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

# Architektura Ogólna Systemu

## Schemat wysokopoziomowy

Dwie osobne aplikacje komunikujące się przez HTTP:

```
Przeglądarka → Vue 3 (port 5173) → [Vite proxy /api → :8000] → Laravel API → MySQL
```

- **Frontend** (Vue 3): odpowiada za UI, routing, zarządzanie stanem
- **Backend** (Laravel): RESTful API, autoryzacja, logika biznesowa, baza danych
- **Między nimi**: tokeny Sanctum (Bearer token w nagłówku HTTP `Authorization: Bearer <token>`)

## Diagram

```plantuml
@startuml System_Architecture
!pragma layout smetana

skinparam backgroundColor #FAFAFA
skinparam defaultFontName Arial
skinparam defaultFontSize 12
skinparam wrapWidth 160

skinparam actor {
  BackgroundColor #1565C0
  BorderColor #0D47A1
  FontColor #FFFFFF
  FontStyle bold
  FontSize 13
}

skinparam rectangle {
  RoundCorner 12
  FontStyle bold
  FontSize 13
  BorderThickness 2
}

skinparam component {
  RoundCorner 8
  BackgroundColor #FFFFFF
  BorderThickness 1
  FontSize 11
}

skinparam database {
  BackgroundColor #FFF8E1
  BorderColor #F9A825
  BorderThickness 2
  FontStyle bold
}

skinparam arrow {
  Color #424242
  FontSize 10
  FontColor #424242
  Thickness 1.5
}

skinparam note {
  BackgroundColor #FFFDE7
  BorderColor #F9A825
  FontSize 10
}

actor "Użytkownik\n<<browser>>" as U

rectangle "FRONTEND  \u00b7  Vue 3 + Vite  \u00b7  port 5173" #EBF5FB {
  component "<<router>>\nVue Router 4\nNavigacja + ochrona ról (beforeEach guard)" as ROUTER #BBDEFB
  component "<<view>>\nViews & Components\nSPA — lazy-loaded per panel" as VIEWS #FFFFFF
  component "<<store>>\nPinia Stores\nauth | main | production | warehouse | notifications" as STORES #E3F2FD
  component "<<http-client>>\nAxios clients:\napi.js (interceptor) | productionApi.js\nwarehouseApi.js | notificationApi.js" as AXIOS #BBDEFB
}

rectangle "BACKEND  \u00b7  Laravel 10  \u00b7  port 8000" #FFF3E0 {
  component "<<middleware>>\nSanctum TokenGuard\n+ CheckRole middleware" as MW #FFE0B2
  component "<<controller>>\n14 HTTP Controllers\nJSON-only responses" as CTRL #FFF8E1
  component "<<service>>\nBusiness Logic Layer\nServices/" as SVC #FFFFFF
  component "<<model>>\nEloquent ORM\n16 Models  |  26 Migrations" as MODELS #FFF3E0
  component "<<event-bus>>\nEvents & Listeners\nLowStockAlert | OrderCompleted | ProductionStarted" as EVENTS #FFE0B2
}

database "MySQL 8.4\n  okna_produkcja\n  ~20 tables" as DB

U -right-> ROUTER : navigates
ROUTER -down-> VIEWS : renders matched page
VIEWS -down-> STORES : reads reactive state /\ninvokes async actions
STORES -down-> AXIOS : dispatches API calls

AXIOS -right-> MW : "HTTP REST  /api/*\nAuthorization: Bearer <token>"

MW -down-> CTRL : passes authenticated request
CTRL -down-> SVC : delegates business logic
SVC -right-> EVENTS : fires domain events
SVC -down-> MODELS : data access via ORM
MODELS -down-> DB : SQL queries
EVENTS ..> MODELS : persists Notification records

note right of AXIOS
  4 dedicated clients (all use /api):
  api.js          — main, has request+response interceptors
  productionApi.js — production orders, manual token
  warehouseApi.js  — deliveries, manual token
  notificationApi.js — notifications, manual token
  api.js also handles 401→redirect, 403→log
end note

note right of MW
  Two-layer auth:
  1. auth:sanctum → validate Bearer token
  2. role:admin|production|warehouse
end note
@enduml
```

## Stos technologiczny

| Warstwa | Technologia | Wersja | Po co |
|---------|-------------|--------|-------|
| Frontend | Vue 3 + Composition API | 3.x | UI, SPA w przeglądarce |
| State | Pinia | 2.x | Globalny stan aplikacji |
| Routing | Vue Router | 4.x | Nawigacja + ochrona ról |
| HTTP | Axios | 1.x | Komunikacja z API |
| Build | Vite | 5.x | Bundler, dev proxy |
| Backend | Laravel | 10.x | REST API, logika biznesowa |
| Auth | Laravel Sanctum | — | Tokeny Bearer |
| ORM | Eloquent | — | Dostęp do MySQL |
| Baza | MySQL | 8.4.3 | Persystencja danych |
| PHP | PHP | 8.3.28 | Środowisko backendu |

## Proxy Vite (jak /api trafia do Laravela)

Plik: `frontend/vite.config.js`

```js
server: {
  proxy: {
    '/api': {
      target: 'http://localhost:8000',
      changeOrigin: true
    }
  }
}
```

**Efekt**: `fetch('/api/materials')` w przeglądarce → Vite proxy → `http://localhost:8000/api/materials`  
Dzięki temu nie ma CORS errors w developmencie i token nie wycieka przez full URL.

## Struktura katalogów

```
vueLavarell/
├── backend/                  ← Laravel 10 API
│   ├── app/
│   │   ├── Http/Controllers/ ← 14 kontrolerów (odpowiedzi JSON)
│   │   ├── Models/           ← 16 modeli Eloquent
│   │   ├── Middleware/       ← auth:sanctum, role:xxx
│   │   ├── Services/         ← logika biznesowa
│   │   ├── Policies/         ← autoryzacja na poziomie modeli
│   │   └── Exceptions/       ← Handler.php (JSON 401 zamiast redirect)
│   ├── database/
│   │   ├── migrations/       ← 26 migracji
│   │   └── seeders/          ← dane testowe
│   └── routes/api.php        ← wszystkie endpointy
│
└── frontend/                 ← Vue 3 + Vite
    └── src/
        ├── views/            ← strony (LoginView, HomeView, etc.)
        │   ├── admin/        ← widoki tylko dla admina
        │   ├── production/   ← widoki panelu produkcji
        │   └── warehouse/    ← widoki magazynu
        ├── stores/           ← Pinia (auth, main, production, warehouse)
        ├── services/         ← api.js, warehouseApi.js (Axios)
        ├── router/index.js   ← definicje tras + guardy
        └── components/       ← komponenty współdzielone
```

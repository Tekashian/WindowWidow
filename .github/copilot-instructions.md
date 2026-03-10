# BertrandSoftware — Copilot Context

## What is this project?

**BB Software** is a large-scale internal ERP system for a Polish window, door and roller-shutter manufacturing company. The system manages the full production lifecycle — from client quote, through multi-line production planning, warehouse logistics, glass tracking, delivery & installation, service requests and finance — all the way to invoicing and archival.

---

## Tech Stack

### Backend
| Item | Value |
|------|-------|
| Framework | Laravel 9.x |
| Language | PHP 8.0+ |
| Database | MySQL (default connection, `mysql`) |
| Auth | Laravel Sanctum — Token-based; token stored in `localStorage` as `"token"`, sent via `Authorization: Token <token>` header |
| Architecture | **Controller → Service → Repository → Model** (4-layer). Thin controllers, business logic in Services, DB queries in Repositories extending `BaseRepository`. |
| Queue | dedicated queues: `downloaders`, `ISL`, `orders`, `TKW`, `KRS`, `CEIDG` |
| Code standard | PSR-4 autoloading, PascalCase class names, camelCase methods |
| Key packages | sanctum, guzzle, awobaz/compoships, doctrine/dbal, pusher |

### Frontend
| Item | Value |
|------|-------|
| Framework | Vue 3.2+ (Composition API + `<script lang="ts">`) |
| Build tool | Vite 4.x (`vite.config.js`) |
| State management | Pinia 2.x — main store: `useRightsStore` (permissions + user data) |
| Routing | Vue Router 4.x |
| HTTP client | Custom `AxiosConfig` base class per module, wrapping `axiosAPI` instance from `@/axiosApi.ts`. **Never call axios directly in components.** |
| UI library | **PrimeVue 3.x** + **Tailwind CSS 3.x** + MDI icons (`@mdi/font`) |
| Language | TypeScript |
| Toast | PrimeVue Toast via `addToast(toast, ToastType, summary?, message?)` from `@/utils/composables/addToast.ts` |
| Key packages | pinia, vue-router, primevue, @tanstack/vue-query, @vueuse/core, @vuelidate/core, pdfmake, chart.js, moment, maz-ui |

### Path aliases (vite.config.js)
```
@        → resources/js
@utils   → resources/js/utils
@modules → resources/js/modules
```

---

## Multi-App Architecture

**CRITICAL**: This is NOT one single-page application. It is **multiple independent Vue applications**, each with its own Vite entry point, router and mount point. Every module lives in `resources/js/modules/<ModuleName>/` and has its own `<module>Main.ts` entry, router and routes namespace.

| Module (entry) | Path prefix | Description |
|---|---|---|
| `pvcMain.ts` | `/pvc` | PVC & Roller Shutter production management |
| `woodAndAluMain.ts` | `/woodAndAlu` | Wood & Aluminium production management |
| `warehouseMain.ts` | `/warehouse` | Warehouse — glass, articles, stands |
| `deliveryAndInstallationPlannerMain.ts` | `/delivery-and-installation-planner` | Logistics & installation scheduling |
| `serviceMain.ts` | `/service` | Post-sale service (ZLS/ZLU repair forms) |
| `analysisMain.ts` | `/analysis` | Sales analysis, client orders, reports |
| `financeMain.ts` | `/finance` | Financial operations, payment management |
| `marketingMain.ts` | `/marketing` | Marketing operations |
| `terminalsPVCMain.ts` | `/terminalsPVC` | PVC production scanning terminals |
| `terminalsWoodMain.ts` | `/terminalsWood` | Wood/Alu scanning terminals |
| `efficiencyMain.ts` | `/efficiency` | Production efficiency dashboards |
| `helpdeskMain.ts` | `/helpdesk` | Internal helpdesk tickets |
| `ISLTerminalMain.ts` | `/ISL-terminal` | ISL inter-warehouse transfer terminals |
| `standsMain.ts` | `/stands` | Window transport stand management |
| `toolsMain.ts` | `/tools` | Utility tools |
| `humanResourcesMain.ts` | `/hr` | HR — users, permissions, departments |
| `main.ts` | `/` | Main dashboard + auth + settings |

---

## Backend Route Files (routes/Modules/)

Routes are split per domain:
`ordersApi.php`, `productionApi.php`, `warehouseApi.php`, `planProductionApi.php`,
`productionPVCApi.php`, `woodAndAluApi.php`, `salesApi.php`, `financeApi.php`,
`deliveryAndInstallationApi.php`, `serviceApi.php`, `analysisApi.php`,
`helpdeskApi.php`, `hlmrpApi.php`, `humanResourcesApi.php`, `terminalApi.php`,
`ISLTerminalApi.php`, `standsNewApi.php`, `standsOldApi.php`, `efficiencyApi.php`,
`marketingApi.php`, `logisticsApi.php`, `commonApi.php`, `glassApi.php`, `toolsApi.php`

---

## Core Domain Models

### Production Lines (Factory enum)
- `PVC` — standard PVC window production line
- `PVC_LS` — special/larger PVC line
- `WOOD` — wood joinery production
- `ALU` — aluminium window/door production
- `ROLLER_SHUTTER` — roller shutter production

### Design Types (what gets manufactured per order position)
`WINDOW`, `DOOR`, `ROLLER_SHUTTER`, `GLASS`, `MOSKITIERA` (insect screen),
`SUWANKA` (sliding door), `HST`, `WERANDOWE` (porch door), `DOOR-P`, `OTHER`,
`DOOR-EI` (fire door), `MB-SLIDE`, `FACADE`, `ACCORDION` (folding door),
`ARTLINE`, `SMOOVIO`, `PSK`, `WINDOWSET`

### Order Status Flow (OrderStatutEnum — integer values)
```
100  QUOTE
200  ORDER_NOT_PROCESSED
210  ORDER_MODIFIED
220  PRINT_CONFIRMATION
240  CHECK_CONFIRMATION
260  FINANCIAL_APPROVAL
280  SEND_CONFIRMATION
398  PUT_ON_HOLD          ← suspend order (critical issue)
399  REACTIVATE_AFTER_PUT_ON_HOLD
400  CONFIRMATION_APPROVAL
420  GLASS_ORDER
499  PREPARATION_FOR_OPTIMIZATION
500  OPTIMIZATION_COMPLETED
501  PARTIAL_OPTIMIZATION
600  COMPLETE_PRODUCTION_LAUNCH
601  PARTIAL_PRODUCTION_LAUNCH
700  END_OF_PRODUCTION
740  DELIVERY_PREPARATION
800  DELIVERY_NOTE
825  DELIVERED_ORDER
850  INVOICING
870  ORDER_DELIVERED_AFTER_INVOICING
900  ARCHIVE
```

### Stock Document Types (StockItemActionEnum)
- `PW` — internal production receipt
- `PZ` — external purchase receipt
- `MM` — internal warehouse transfer
- `WZ` — external delivery document

### Key Models and Their Purpose
| Model | Table | Purpose |
|-------|-------|---------|
| `ClientOrders` | `client_orders2` | Client-level order (can contain multiple production orders) |
| `ClientOrdersItems` | `client_orders_items2` | Line items within a client order |
| `Order` | `orders` | Production order (linked to one ClientOrdersItem) |
| `Design` | `designs` | Single product position within a production order |
| `Item` | `items` | Individual manufactured unit (barcode-level) |
| `OrderLog` | `order_logs` | Immutable audit log of every order status change |
| `OrderPlan` | `order_plans` | Production schedule entry for an order |
| `Barcode` | `barcodes` | Barcode assigned to manufactured Item |
| `TerminalScan` | `terminal_scans` | Worker scan on production terminal |
| `GlassStock` | `glass_stocks` | Glass inventory entries |
| `WindowStand` | `window_stands` | Transport stands for finished products |
| `WarehouseArticle` | `warehouse_articles` | Physical warehouse article (accessories, materials) |
| `StockItem` | `stock_items` | Stock level record per article |
| `StockActionLog` | `stock_action_logs` | Immutable stock movement log |
| `Delivery` | `deliveries` | Delivery to client |
| `DeliveryNote` | `delivery_notes` | WZ delivery note document |
| `Shipment` | `shipments` | Links order to a delivery |
| `ClientOrderStages` | `client_order_stages2` | Invoicing stages for phased orders |
| `TKW` | `t_k_w_s` | Cost/price calculation (TKW = Techniczny Koszt Wytworzenia) |
| `IslWarehouseArticlesRequirementList` | `isl_warehouse_articles_requirement_lists` | ISL material demand lists |
| `ZlsForm` | `zls_forms` | Service intervention form (ZLS) |
| `ZluForm` | `zlu_forms` | Service check form (ZLU) |

---

## Permission System

Permissions are **granular per-user**, stored in the `permissions` table (one row per user). Each column is a `0/1` flag representing a specific feature access.

**Backend check** — helper function `getUserFromHeader($request)` used in controllers.

**Frontend check** — `useRightsStore().checkPermission('permission_code')` returns `boolean`.
The store is loaded globally before any navigation and available in all modules.

Known permission codes include: `production_settings`, `shifts`, `complete_calendar`, `bzam`, `nowind`, `supplies`, `production_schedule`, `terminals_pvc`, `terminals_wood`, `warehouse_articles`, `hlmrp`, `human_resources`, `marketing`, `isl_terminal`, `db_docs`, `wz_delete`, `client_view_from_production`, `wood_tech`, `pvc_tech`, `user_session_logs`, `logistics_email_management`, `satisfaction_surveys` and many more.

---

## Global Frontend Utilities

### Components (from `@/utils/components/`)
`<BBButton>`, `<BBInput>`, `<BBInputDate>`, `<BBInputNumber>`, `<BBSelect>`,
`<BBMultiSelect>`, `<BBListBox>`, `<BBCheckbox>`, `<BBRadio>`, `<BBTable>`,
`<BBDataTable>`, `<BBModal>` / `<BaseModal>`, `<BBConfirmDialog>`, `<BBSpinner>`,
`<BBDrawer>`, `<BBOverlay>`, `<BBTextarea>`, `<BBSpeedDial>`, `<Dashboard>`,
`<PanelFrame>`, `<PanelFrameWithDrawer>`, `<Breadcrumbs>`, `<BackBtn>`

### Composables / Utilities
- `addToast(toast, ToastType.SUCCESS|ERROR|WARNING|INFO, summary?, message?)` — show toast
- `ToastType` enum — SUCCESS, ERROR, WARNING, INFO, PERMISSION_ERROR
- `useRightsStore()` — access user, permissions, `checkPermission(code)`
- `errorHandler` composable — from `@/utils/composables`
- `useWindowSize()` — from `@vueuse/core`
- `useCreateVueApp(router)` — factory for all module Vue app instances

---

## External Integrations

| System | Purpose |
|--------|---------|
| **Winpro** | Primary CAD/quoting software; orders are imported via `AllWinproController` (`importWinproAllOrders`, `importWinproBBOrders`, `importWinproWoodOrders`) |
| **Logikal** | Alternative window design import (`LogikalImportController`) |
| **SAGE** | ERP/accounting — warehouse articles sync via `WarehouseSageArticle` |
| **CEIDG** | Polish business registry (sole traders) — downloaded via dedicated queue |
| **KRS** | Polish business registry (companies) — downloaded via dedicated queue |
| **Pusher** | Real-time events (Laravel Echo + pusher-js) |

---

## Critical Business Rules

1. **Order status changes MUST go through dedicated service logic** — never update `status` column directly on the model.
2. **Every order status change is logged** in `OrderLog` — this is an immutable audit trail.
3. **Wood orders require technological confirmation** (`technology_confirmed` flag) before production launch.
4. **Production confirmation of an order ≠ status change** — it is a separate field.
5. **Every stock movement is logged** in `StockActionLog` with reason, user ID and document type (PW/PZ/MM/WZ).
6. **Glass is tracked with its own stands** (`WindowStand`) — each stand has a barcode and log.
7. **Barcodes are assigned per manufactured Item** — terminal scans (`TerminalScan`) reference Item + Design.
8. **TKW (cost price)** is calculated per client order item — never modify it without going through `ItemTkw` / `TKW` models and their service logic.
9. **ISL transfers** require a `IslWarehouseArticlesRequirementList` before execution — do not skip this step.
10. **ClientOrders operates on `client_orders2` table** (not `client_orders`) — the `2` tables are the active production tables after a data migration.
11. **Winpro is the source of truth** for design dimensions and configurations — BB Software receives and displays them, not recalculates them.
12. **Other order types**: BZAM = special batch accessories orders, NOWIND = special non-window orders — they use separate flows within `OtherOrderController`.
13. **DB transactions required** for any operation touching multiple tables (especially order + log + plan).

---

## Naming Conventions

| Layer | Convention |
|-------|-----------|
| PHP classes | PascalCase |
| PHP methods | camelCase |
| DB columns | snake_case |
| Vue components | PascalCase `.vue` files |
| TypeScript composables | `use` prefix, camelCase |
| TypeScript enums | PascalCase enum name, UPPER_SNAKE case values |
| Route files | camelCase (e.g., `ordersApi.php`) |
| API URL paths | camelCase and kebab-case mixed (follow existing pattern in route file) |
| CSS classes | Tailwind utility classes; custom classes in kebab-case |

---

## Development Environment

- Backend: `php artisan serve` → `http://localhost:8000`
- Frontend: `npm run dev` → `http://localhost:5173`
- Queue workers: run separately per queue name (see `.vscode/tasks.json`)
- Schedule: `php artisan schedule:work`
- API base URL: `http://localhost:8000/api` (or `VITE_AXIOS_URL` env var)

---

## Where to Find Things

| What | Where |
|------|-------|
| Route definitions | `routes/Modules/*.php` |
| Controllers | `app/Http/Controllers/<Domain>/` |
| Services | `app/Services/<Domain>/` |
| Repositories | `app/Repositories/<Domain>/` |
| Models | `app/Models/` |
| Enums (PHP) | `app/Enums/` |
| Constants (PHP) | `app/Http/Helpers/helpers.php` |
| Migrations | `database/migrations/` |
| Vue module entry | `resources/js/modules/<Module>/<module>Main.ts` |
| Module routes | `resources/js/routes/<Module>/router.ts` + `routes.ts` |
| Module pages | `resources/js/modules/<Module>/pages/` |
| Module API layer | `resources/js/modules/<Module>/api/` |
| Global components | `resources/js/utils/components/` |
| Global composables | `resources/js/utils/composables/` |
| Global constants | `resources/js/utils/constants/` |
| Permission store | `resources/js/stores/rights.ts` |
| Permission codes (TS) | `resources/js/utils/constants/permissions/<Module>/` |

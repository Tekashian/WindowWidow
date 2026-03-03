# State Machine — Cykl Życia Zlecenia Produkcyjnego

## Diagram (State Machine)

```plantuml
@startuml Production_Order_StateMachine

skinparam backgroundColor #FAFAFA
skinparam defaultFontName Arial
skinparam defaultFontSize 12

skinparam state {
  BorderThickness 2
  FontSize 12
  ArrowColor #455A64
  ArrowFontSize 10
  ArrowFontColor #37474F
  StartColor #1565C0
  EndColor #1565C0
}

skinparam note {
  BackgroundColor #FFFDE7
  BorderColor #FBC02D
  FontSize 10
}

' ─── PRODUCTION ORDER LIFECYCLE ───
state "pending\n<<awaiting start>>" as S_PENDING #E3F2FD
state "materials_check\n<<checking stock>>" as S_MATCHECK #FFF9C4
state "materials_reserved\n<<stock reserved>>" as S_MATRES #FFF9C4
state "in_progress\n<<manufacturing>>" as S_WIP #FFCC80
state "quality_check\n<<QC verification>>" as S_QC #FFF9C4
state "completed\n<<finished>>" as S_DONE #E8F5E9
state "shipped_to_warehouse\n<<all batches sent>>" as S_SHIPPED #C8E6C9
state "on_hold\n<<blocked>>" as S_HOLD #FFCDD2
state "cancelled\n<<terminated>>" as S_CANCEL #FFEBEE

[*] -> S_PENDING : POST /production/orders\n**role: admin | production**

note right of S_PENDING
  <b>confirmOrder()</b> is called here:
  POST .../confirm
  — does NOT change status
  — sets confirmed_by_production = true
  — sets estimated_completion_at
  — notifies admin
  Status stays <b>pending</b> until start
end note

S_PENDING --> S_WIP       : POST .../start\n**role: admin | production**\n(requires status == 'pending')

S_PENDING     -right-> S_MATCHECK    : POST .../update-status\nstatus=materials_check
S_MATCHECK    --> S_MATRES           : POST .../update-status\nstatus=materials_reserved
S_MATRES      --> S_WIP              : POST .../update-status\nstatus=in_progress

S_WIP --> S_WIP : POST .../create-batch\nPOST .../update-progress\nPOST .../report-issue\nPOST .../report-delay

note right of S_WIP
  <b>Actions while in-progress:</b>
  create-batch     → creates ProductionBatch(in_production)
  update-progress  → set sub-status in update-status
  report-issue     → creates ProductionIssue
  report-delay     → flag + revised_completion_at
end note

S_WIP         --> S_QC               : POST .../update-progress\nstatus=quality_check
S_QC          --> S_DONE             : POST .../update-progress\nstatus=completed
S_WIP         --> S_DONE             : POST .../update-progress\nstatus=completed

S_DONE        --> S_SHIPPED          : POST .../ship-to-warehouse\n(all batches shipped)\n**role: admin | production**

S_WIP         --> S_HOLD             : POST .../report-issue\n(severity=critical → auto)
S_HOLD        --> S_WIP              : POST .../update-status\nstatus=in_progress

S_PENDING     -down-> S_CANCEL : POST .../update-status\n**role: admin**
S_MATCHECK    -down-> S_CANCEL
S_MATRES      -down-> S_CANCEL
S_WIP         -down-> S_CANCEL
S_HOLD        -down-> S_CANCEL

S_SHIPPED --> [*]

note bottom of S_SHIPPED
  <b>ship-to-warehouse</b> (per batch):
  1. Batch must have status <b>ready</b>
  2. Creates WarehouseDelivery (status: pending)
  3. Notifies warehouse users
  4. When ALL batches shipped → order status = shipped_to_warehouse
end note

' ─── WAREHOUSE DELIVERY LIFECYCLE ───
state "WarehouseDelivery\n<<parallel lifecycle>>" as WD {
  state "pending\n<<created, not yet in transit>>"   as WD_P  #E3F2FD
  state "in_transit\n<<shipped by warehouse>>"        as WD_T  #FFF9C4
  state "delivered\n<<received by warehouse>>"        as WD_D  #E8F5E9
  state "rejected\n<<returned>>"                      as WD_R  #FFEBEE
  state "partial\n<<partially received>>"             as WD_PA #FFF8E1

  [*]   --> WD_P
  WD_P  --> WD_T  : POST .../ship\n**role: warehouse | admin**
  WD_T  --> WD_D  : POST .../receive\n**role: warehouse | admin**
  WD_T  --> WD_R  : POST .../reject\n(notes REQUIRED)\n**role: warehouse | admin**
  WD_T  --> WD_PA : (partial receipt)
  WD_D  --> [*]
  WD_R  --> [*]
  WD_PA --> [*]
}

S_DONE -down-> WD : auto-created on\neach ship-to-warehouse call

note right of WD_D
  On <b>receive</b>:
  received_by = warehouse user id
  received_at = now()
  actual_delivery_date = now()
end note

note left of WD_R
  <b>reject</b> requires 'notes' field
  (stored in rejection_reason column)
  Notifies production team
end note
@enduml
```

## Opis każdego stanu

### `pending` — Oczekujące
- Zlecenie właśnie stworzone przez admina lub produkcję
- Można wywołać `confirm` → ustawia flagę `confirmed_by_production=true` **bez zmiany statusu**
- Czeka na `start` (wymaga statusu `pending`)

### `confirmed` — Potwierdzone (FLAGA, nie status DB!)
- `confirmOrder()` ustawia `confirmed_by_production = true` + `estimated_completion_at`
- Status zlecenia **pozostaje `pending`** — `startProduction` wymaga status `pending`
- To celowy design: potwierdzenie jest odnotowane, ale status nie blokuje startu

### `in_progress` / `materials_check` / `materials_reserved` / `quality_check` — W trakcie
- `in_progress` = aktywna praca na hali (ustawiane przez `startProduction` lub `update-status`)
- `materials_check`, `materials_reserved` = opcjonalne etapy pośrednir przez `update-status`
- `quality_check` = kontrola jakości przed `completed`
- Możliwe tworzenie parti (`ProductionBatch`), zgłaszanie problemów (`ProductionIssue`)

### `completed` — Ukończone
- Ustawiany przez `update-progress` (status=completed) lub `update-status`
- Produkcja fizycznie skończona
- Wysyłka do magazynu odbywa się osobno przez `ship-to-warehouse` (niezależnie od tego statusu)

### `shipped_to_warehouse` — Wszystkie partie wysłane
- Ustawiany **automatycznie** gdy WSZYSTKIE partie produktu mają status `shipped`
- Wywołanie `ship-to-warehouse` tworzy rekord `WarehouseDelivery` (status: `pending`) per partię

### `on_hold` — Wstrzymane
- Automatycznie ustawiany gdy issue ma `severity=critical`
- Można cofnąć do `in_progress` przez `update-status`
- Można anulować

### `cancelled` — Anulowane
- Może nastąpić z każdego stanu (oprócz `shipped_to_warehouse`)
- Tylko admin

---

## Cykl WarehouseDelivery

| Status | Kto zmienia | API endpoint |
|--------|-------------|-------------|
| `pending` | Automatycznie przy tworzeniu dostawy | — |
| `in_transit` | Magazyn | `POST /warehouse/deliveries/:id/ship` |
| `delivered` | Magazyn | `POST /warehouse/deliveries/:id/receive` |
| `rejected` | Magazyn | `POST /warehouse/deliveries/:id/reject` (pole `notes` wymagane) |
| `partial` | Magazyn | (częściowe odebranie) |

## Events (Laravel Event System)

```
ProductionStarted         → NotifyWarehouseAboutDelivery listener
ProductionOrderCompleted  → NotifyProductionOrderCompleted listener
LowStockAlert             → NotifyLowStock listener
```

Eventy tworzą rekordy w tabeli `notifications` dla odpowiednich użytkowników.

## Przepływ ról w procesie produkcyjnym

```
admin        → tworzy zlecenie (POST /production/orders)  ← role: admin | production
               ↓
admin/prod   → (opcjonalnie) potwierdza flagę (POST .../confirm) — status nadal pending
               ↓
production   → startuje (POST .../start)  ← wymaga status == 'pending'
               ↓
production   → pracuje: update-progress (materials_check, materials_reserved,
                          in_progress, quality_check, completed)
               ↓  
production   → tworzy parti (create-batch) i oznacza jako ready
               ↓
production   → wysyła parti do magazynu (ship-to-warehouse) ← tworzy WarehouseDelivery
               ↓
warehouse    → odbiera dostawę (POST /warehouse/deliveries/:id/receive)
               ↓
             GOTOWE ✓
```

## ProductionBatch — Dlaczego?

Duże zamówienia (np. 500 okien) są dzielone na mniejsze partie produkcyjne.  
Każda partia ma własny status i można ją śledzić osobno.

```
ProductionOrder (500 szt)
├── ProductionBatch #1 (167 szt) — completed
├── ProductionBatch #2 (167 szt) — in_progress  
└── ProductionBatch #3 (166 szt) — pending
```

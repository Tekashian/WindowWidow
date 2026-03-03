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
state "pending\n<<awaiting confirmation>>" as S_PENDING #E3F2FD
state "confirmed\n<<accepted by production>>" as S_CONFIRMED #E8EAF6
state "in_progress\n<<manufacturing>>" as S_WIP #FFF9C4
state "completed\n<<sent to warehouse>>" as S_DONE #E8F5E9
state "cancelled\n<<terminated>>" as S_CANCEL #FFEBEE

[*] -> S_PENDING : POST /production/orders\n**role: admin**

S_PENDING   --> S_CONFIRMED : POST .../confirm\n**role: admin | production**
S_CONFIRMED --> S_WIP       : POST .../start\n**role: admin | production**

S_WIP --> S_WIP : POST .../create-batch\nPOST .../update-progress\nPOST .../report-issue\nPOST .../report-delay

note right of S_WIP
  <b>Actions while in-progress:</b>
  create-batch     → creates ProductionBatch
  update-progress  → sets progress 0–100%
  report-issue     → creates ProductionIssue
  report-delay     → updates timeline
end note

S_WIP --> S_DONE    : POST .../ship-to-warehouse\n**role: admin | production**
S_DONE --> [*]

S_PENDING   -right-> S_CANCEL : POST .../update-status\n**role: admin**
S_CONFIRMED -right-> S_CANCEL
S_WIP       -right-> S_CANCEL

note bottom of S_DONE
  On transition to <b>completed</b>:
  1. Creates WarehouseDelivery record (status: pending)
  2. Fires Event: ProductionOrderCompleted
  3. Listener sends Notification to warehouse users
end note

' ─── WAREHOUSE DELIVERY LIFECYCLE ───
state "WarehouseDelivery\n<<parallel lifecycle>>" as WD {
  state "pending\n<<awaiting shipment>>"   as WD_P  #E3F2FD
  state "in_transit\n<<on the way>>"        as WD_T  #FFF9C4
  state "delivered\n<<stock updated>>"      as WD_D  #E8F5E9
  state "rejected\n<<returned>>"            as WD_R  #FFEBEE

  [*]   --> WD_P
  WD_P  --> WD_T : POST .../ship\n**role: warehouse | admin**
  WD_T  --> WD_D : POST .../receive\n**role: warehouse | admin**
  WD_T  --> WD_R : POST .../reject (reason required)\n**role: warehouse | admin**
  WD_D  --> [*]
  WD_R  --> [*]
}

S_DONE -down-> WD : auto-created

note right of WD_D
  On <b>receive</b>:
  stock_movements INSERT (type: in)
  materials.quantity += received_qty
end note
@enduml
```

## Opis każdego stanu

### `pending` — Oczekujące
- Zlecenie właśnie stworzone przez admina
- Dane: order_number, deadline, lista produktów do wykonania
- Czeka na potwierdzenie przez produkcję

### `confirmed` — Potwierdzone  
- Wydział produkcji przyjął zlecenie
- Materiały zarezerwowane
- Czeka na fizyczne uruchomienie linii

### `in_progress` — W trakcie produkcji
- Aktywna praca na hali
- Możliwe tworzenie partii (`ProductionBatch`)
- Możliwe zgłaszanie problemów (`ProductionIssue`)
- Pole `progress_percentage` aktualizowane przez `update-progress`

### `completed` — Ukończone
- Produkcja skończyła
- **Automatycznie** tworzony jest rekord `WarehouseDelivery` (status: pending)
- Wysyłany jest Event `ProductionOrderCompleted` → powiadomienie dla magazynu

### `cancelled` — Anulowane
- Może nastąpić z każdego stanu
- Tylko admin

---

## Cykl WarehouseDelivery

| Status | Kto zmienia | API endpoint |
|--------|-------------|-------------|
| `pending` | Automatycznie (system) | — |
| `in_transit` | Magazyn | `POST /warehouse/deliveries/:id/ship` |
| `delivered` | Magazyn | `POST /warehouse/deliveries/:id/receive` |
| `rejected` | Magazyn | `POST /warehouse/deliveries/:id/reject` |

## Events (Laravel Event System)

```
ProductionStarted         → NotifyWarehouseAboutDelivery listener
ProductionOrderCompleted  → NotifyProductionOrderCompleted listener
LowStockAlert             → NotifyLowStock listener
```

Eventy tworzą rekordy w tabeli `notifications` dla odpowiednich użytkowników.

## Przepływ ról w procesie produkcyjnym

```
admin        → tworzy zlecenie (POST /production/orders)
               ↓
production   → potwierdza (confirm) i startuje (start)  
               ↓
production   → pracuje: create-batch, update-progress, report-issue
               ↓  
production   → wysyła do magazynu (ship-to-warehouse)
               ↓
warehouse    → odbiera dostawę (receive)
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

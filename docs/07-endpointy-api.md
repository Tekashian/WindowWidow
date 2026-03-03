# Pełna Lista Endpointów REST API

## Legenda
- 🟢 Publiczny (bez tokenu)
- 🔵 Zalogowany (wszystkie role)
- 🟡 Admin + Warehouse
- 🟠 Admin + Production
- 🔴 Admin only

---

## Autentykacja

| Metoda | Endpoint | Rola | Opis |
|--------|----------|------|------|
| POST | `/api/login` | 🟢 | Logowanie, zwraca token |
| POST | `/api/logout` | 🔵 | Wylogowanie, unieważnia token |
| GET | `/api/me` | 🔵 | Dane zalogowanego użytkownika |
| GET | `/api/health` | 🟢 | Sprawdzenie stanu API |

**Odpowiedź POST /login:**
```json
{
  "user": { "id": 1, "name": "Admin", "email": "...", "role": "admin" },
  "token": "1|xyz123..."
}
```

---

## Dashboard

| Metoda | Endpoint | Rola | Opis |
|--------|----------|------|------|
| GET | `/api/dashboard` | 🔵 | Statystyki ogólne |
| GET | `/api/dashboard/export-materials` | 🔵 | Export CSV materiałów |

---

## Okna (Windows)

| Metoda | Endpoint | Rola | Opis |
|--------|----------|------|------|
| GET | `/api/windows` | 🔵 | Lista okien (paginacja) |
| GET | `/api/windows/{id}` | 🔵 | Szczegóły okna |
| POST | `/api/windows` | 🔴 | Nowe okno |
| PUT | `/api/windows/{id}` | 🔴 | Edytuj okno |
| DELETE | `/api/windows/{id}` | 🔴 | Usuń okno |
| POST | `/api/windows/{id}/update-stock` | 🔴 | Zmień stan magazynowy |

---

## Profile

| Metoda | Endpoint | Rola | Opis |
|--------|----------|------|------|
| GET | `/api/profiles` | 🔵 | Lista profili |
| GET | `/api/profiles/{id}` | 🔵 | Szczegóły profilu |
| POST | `/api/profiles` | 🔴 | Nowy profil |
| PUT | `/api/profiles/{id}` | 🔴 | Edytuj profil |
| DELETE | `/api/profiles/{id}` | 🔴 | Usuń profil |

---

## Szyby (Glasses)

| Metoda | Endpoint | Rola | Opis |
|--------|----------|------|------|
| GET | `/api/glasses` | 🔵 | Lista szyb |
| GET | `/api/glasses/{id}` | 🔵 | Szczegóły szyby |
| POST | `/api/glasses` | 🔴 | Nowy typ szyby |
| PUT | `/api/glasses/{id}` | 🔴 | Edytuj szybę |
| DELETE | `/api/glasses/{id}` | 🔴 | Usuń szybę |

---

## Zamówienia Klientów (Orders)

| Metoda | Endpoint | Rola | Opis |
|--------|----------|------|------|
| GET | `/api/orders` | 🔵 | Lista zamówień (paginacja) |
| GET | `/api/orders/{id}` | 🔵 | Szczegóły zamówienia |
| POST | `/api/orders` | 🔴 | Nowe zamówienie |
| PUT | `/api/orders/{id}` | 🔴 | Edytuj zamówienie |
| DELETE | `/api/orders/{id}` | 🔴 | Usuń zamówienie |
| POST | `/api/orders/{id}/update-status` | 🔴 | Zmień status |

---

## Materiały (Materials)

| Metoda | Endpoint | Rola | Opis |
|--------|----------|------|------|
| GET | `/api/materials` | 🔵 | Lista materiałów (paginacja) |
| GET | `/api/materials/{id}` | 🟡 | Szczegóły materiału |
| POST | `/api/materials` | 🟡 | Nowy materiał |
| PUT | `/api/materials/{id}` | 🟡 | Edytuj materiał |
| DELETE | `/api/materials/{id}` | 🟡 | Usuń materiał |
| POST | `/api/materials/{id}/add-stock` | 🟡 | Dodaj do stanu |
| POST | `/api/materials/{id}/remove-stock` | 🟡 | Pobierz ze stanu |
| GET | `/api/materials/{id}/movements` | 🔵 | Historia ruchów magazynowych |
| GET | `/api/low-stock` | 🔵 | Materiały poniżej min_stock |

**Body dla add-stock/remove-stock:**
```json
{ "quantity": 50, "reason": "Dostawa od dostawcy X" }
```

---

## Produkcja — Zlecenia

| Metoda | Endpoint | Rola | Opis |
|--------|----------|------|------|
| GET | `/api/production/orders` | 🟠 | Lista zleceń |
| POST | `/api/production/orders` | 🟠 | Nowe zlecenie |
| GET | `/api/production/orders/statistics` | 🟠 | Statystyki produkcji |
| GET | `/api/production/orders/{id}` | 🟠 | Szczegóły zlecenia |
| PUT | `/api/production/orders/{id}` | 🟠 | Edytuj zlecenie |
| DELETE | `/api/production/orders/{id}` | 🟠 | Usuń zlecenie |
| POST | `/api/production/orders/{id}/start` | 🟠 | Wystartuj produkcję |
| POST | `/api/production/orders/{id}/confirm` | 🟠 | Potwierdź zlecenie |
| POST | `/api/production/orders/{id}/report-delay` | 🟠 | Zgłoś opóźnienie |
| POST | `/api/production/orders/{id}/update-progress` | 🟠 | Aktualizuj postęp (%) |
| POST | `/api/production/orders/{id}/update-status` | 🟠 | Zmień status |
| POST | `/api/production/orders/{id}/report-issue` | 🟠 | Zgłoś problem |
| POST | `/api/production/orders/{id}/create-batch` | 🟠 | Utwórz partię |
| POST | `/api/production/orders/{id}/ship-to-warehouse` | 🟠 | Wyślij do magazynu |

---

## Produkcja — Partie i Problemy

| Metoda | Endpoint | Rola | Opis |
|--------|----------|------|------|
| GET | `/api/production/batches` | 🟠 | Lista partii |
| GET | `/api/production/batches/{id}` | 🟠 | Szczegóły partii |
| POST | `/api/production/batches/{id}/update-status` | 🟠 | Zmień status partii |
| GET | `/api/production/issues` | 🟠 | Lista problemów |
| GET | `/api/production/issues/statistics` | 🟠 | Statystyki problemów |
| GET | `/api/production/issues/{id}` | 🟠 | Szczegóły problemu |
| POST | `/api/production/issues/{id}/update-status` | 🟠 | Zmień status problemu |
| POST | `/api/production/issues/{id}/resolve` | 🟠 | Rozwiąż problem |

---

## Magazyn — Dostawy

| Metoda | Endpoint | Rola | Opis |
|--------|----------|------|------|
| GET | `/api/warehouse/deliveries` | 🟡 | Lista dostaw |
| GET | `/api/warehouse/deliveries/statistics` | 🟡 | Statystyki (pending/in_transit/delayed) |
| GET | `/api/warehouse/deliveries/{id}` | 🟡 | Szczegóły dostawy |
| POST | `/api/warehouse/deliveries/{id}/ship` | 🟡 | Wyślij dostawę |
| POST | `/api/warehouse/deliveries/{id}/receive` | 🟡 | Odbierz dostawę |
| POST | `/api/warehouse/deliveries/{id}/reject` | 🟡 | Odrzuć dostawę |

**Odpowiedź GET /warehouse/deliveries/statistics:**
```json
{
  "pending": 3,
  "in_transit": 1,
  "delayed": 1,
  "delivered_today": 0
}
```

---

## Powiadomienia

| Metoda | Endpoint | Rola | Opis |
|--------|----------|------|------|
| GET | `/api/notifications` | 🔵 | Lista powiadomień |
| GET | `/api/notifications/unread-count` | 🔵 | Liczba nieprzeczytanych |
| POST | `/api/notifications/mark-all-read` | 🔵 | Oznacz wszystkie jako przeczytane |
| POST | `/api/notifications/{id}/mark-read` | 🔵 | Oznacz jedno jako przeczytane |
| DELETE | `/api/notifications/{id}` | 🔵 | Usuń powiadomienie |
| DELETE | `/api/notifications/read/all` | 🔵 | Usuń wszystkie przeczytane |

---

## Inne

| Metoda | Endpoint | Rola | Opis |
|--------|----------|------|------|
| GET | `/api/production/products` | 🔵 | Dostępne produkty do produkcji |
| GET | `/api/production/company-settings` | 🔵 | Ustawienia firmy |
| POST | `/api/upload/image` | 🔴 | Upload zdjęcia okna |
| DELETE | `/api/upload/image` | 🔴 | Usuń zdjęcie |

---

## Format odpowiedzi paginowanej

Większość list (materials, windows, orders) zwraca paginację Laravel:

```json
{
  "current_page": 1,
  "data": [ ...rekordy... ],
  "from": 1,
  "last_page": 4,
  "per_page": 15,
  "to": 15,
  "total": 50
}
```

> W kodzie frontend dlatego używamy `response.data.data` (nie `response.data`)!

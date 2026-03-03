# Ściągawka na Prezentację u Pracodawcy

## TL;DR — co powiedzieć w 30 sekund

> "Zbudowałem system zarządzania produkcją okien w architekturze SPA + REST API.
> Frontend to Vue 3 z Compositon API i Pinia, backend to Laravel 10 z Sanctum.
> System obsługuje trzy role: admin, produkcja, magazyn — każda ma własny panel z innymi uprawnieniami.
> Całość śledzi cykl życia zlecenia od zamówienia klienta przez hale produkcyjną aż do magazynu."

---

## Stos technologiczny (recytuj pewnie)

| Warstwa | Technologia | Dlaczego taka |
|---------|-------------|---------------|
| Frontend | **Vue 3** + Composition API | Reaktywny UI, SPA — brak przeładowań |
| State | **Pinia** | Natywny store Vue 3, TypeScript-friendly |
| Routing | **Vue Router 4** | Czyste URL + ochrona tras przez role |
| HTTP | **Axios** | Interceptory (automatyczny Bearer token) |
| Build | **Vite** | ~100ms HMR, proxy do backendu |
| Backend | **Laravel 10** | PHP 8.3, Eloquent ORM, REST API |
| Auth | **Laravel Sanctum** | Tokeny Bearer, przechowywane w DB |
| Baza | **MySQL 8** | Relacyjna, 20 tabel |

---

## 5 kluczowych konceptów — naucz się na pamięć

### 1. Bearer Token (jak auth działa)
```
Login → Laravel zwraca token → localStorage["token"]
→ Axios interceptor → Authorization: Bearer <token>
→ Laravel Sanctum weryfikuje → request accepted
```
Token wygasa po **30 minutach**.

### 2. Paginacja Laravel (częsty bug!)
```javascript
// Laravel zwraca: { data: [...], current_page, total }
// NIEPOPRAWNE:
materials.value = response.data       // ← iteruje po obiekcie!
// POPRAWNE:
materials.value = response.data.data  // ← tablica rekordów
```

### 3. Trzy role = trzy panele
- `admin` → wszystko + `/admin/*`
- `production` → `/production/*` (zlecenia, partie, problemy)
- `warehouse` → `/warehouse/*` (dostawy, materiały)

### 4. Vite Proxy (bez CORS w dev)
```javascript
// vite.config.js
proxy: { '/api': 'http://localhost:8000' }
// /api/materials → localhost:8000/api/materials
```

### 5. State Machine dla zamówień
```
pending → confirmed → in_progress → completed
```
Każde przejście = osobny API endpoint (nie przez PUT!)

---

## Pytania rekrutera i odpowiedzi

**Q: Dlaczego Vue zamiast React?**
A: Vue 3 daje pełny Composition API, built-in state management (Pinia), i jest bardzo czytelny dla PHP developerów. Mniejszy boilerplate niż React + Redux.

**Q: Dlaczego SPA a nie SSR/SSG?**
A: To aplikacja wewnętrzna firmy — SEO jest nieistotne. Priorytet to reaktywność UI bez przeładowań strony i bogate interakcje (drag & drop partii, live updates statusów).

**Q: Dlaczego Sanctum zamiast JWT?**
A: Sanctum jest natywny dla Laravel, tokeny są w DB (można je unieważniać — np. przy wylogowaniu lub zmianie hasła). JWT jest stateless i nie można go cofnąć przed wygaśnięciem.

**Q: Jak zabezpieczyłeś endpointy?**
A: Dwie warstwy. Pierwsza: `auth:sanctum` middleware — sprawdza Bearer token przy każdym żądaniu. Druga: `role:xxx` middleware — sprawdza pole `role` w tabeli `users`. Dodatkowo Policies dla wrażliwych modeli.

**Q: Skąd wiesz że token jest ważny na frontendzie?**
A: `isAuthenticated` to Vue `computed` — oblicza `Date.now() < tokenExpiry`. Vue Router `beforeEach` guard sprawdza tę wartość przy każdej nawigacji. Jeśli expired → redirect na `/login`.

**Q: Co się dzieje gdy produkcja wyśle towar do magazynu?**
A: `POST /production/orders/:id/ship-to-warehouse` — Laravel zmienia status zlecenia na `completed`, automatycznie tworzy rekord `WarehouseDelivery` (status: pending), wysyła Event `ProductionOrderCompleted` który przez Listener tworzy Notification dla magazynierów.

**Q: Jak śledziłeś zmiany stanów magazynowych?**
A: Każda zmiana stanu materiału (dodanie/zużycie) tworzy rekord w `stock_movements` z polem `type` (in/out), `quantity` i `reason`. To daje pełny audit trail.

**Q: Jak rozwiązałeś problem z PHP 8?**
A: `Handler::unauthenticated(Request $request, ...)` był niezgodny sygnaturowo z klasą nadrzędną. PHP 8 zamienił to z warning na fatal error. Fix: usunięcie type hinta z parametru.

---

## Architektura — jeden diagram do pokazania

```
Browser
  │
  ▼
Vue 3 (port 5173)
  ├── Vue Router (nawigacja + ochrona ról)
  ├── Pinia (globalny stan: user, materials, orders...)
  └── Axios (HTTP client + interceptor Bearer token)
       │
       │ GET/POST/PUT/DELETE /api/...
       ▼
Laravel 10 (port 8000)
  ├── Sanctum Middleware (weryfikuje token)
  ├── Role Middleware (sprawdza role z DB)
  ├── Controllers (logika, JSON responses)
  ├── Eloquent Models (ORM)
  └── Events + Listeners (notyfikacje)
       │
       ▼
MySQL 8 (okna_produkcja, ~20 tabel)
```

---

## Dane testowe do demo

| Co | Wartość |
|----|---------|
| Admin login | admin@windowwidow.pl / admin123 |
| Produkcja login | produkcja@windowwidow.pl / prod123 |
| Magazyn login | magazyn@windowwidow.pl / mag123 |
| Backend URL | http://localhost:8000 |
| Frontend URL | http://localhost:5173 |

## Start przed prezentacją

```powershell
# Terminal 1 — backend
Set-Location C:\Users\Admin\Desktop\vueLavarell\backend
C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe artisan serve --host=127.0.0.1 --port=8000

# Terminal 2 — frontend  
Set-Location C:\Users\Admin\Desktop\vueLavarell\frontend
npm run dev
```

Otwórz: **http://localhost:5173**

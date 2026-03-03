# Dane logowania - System Zarządzania Produkcją Okien

## Środowisko: Lokalne (Development)

### Backend API
- **URL:** http://127.0.0.1:8000
- **Health Check:** http://127.0.0.1:8000/api/health

### Frontend
- **URL:** http://localhost:5173 (Vite dev server)

---

## Konta Użytkowników

### 👨‍💼 Administrator
- **Email:** `admin@windowwidow.pl`
- **Hasło:** `admin123`
- **Rola:** `admin`
- **Uprawnienia:** Pełny dostęp do wszystkich paneli (Admin, Produkcja, Magazyn)

### 🏭 Produkcja
- **Email:** `produkcja@windowwidow.pl`
- **Hasło:** `prod123`
- **Rola:** `production`
- **Uprawnienia:** Dostęp do panelu produkcji, zarządzanie zleceniami, zgłaszanie problemów

### 📦 Magazyn
- **Email:** `magazyn@windowwidow.pl`
- **Hasło:** `mag123`
- **Rola:** `warehouse`
- **Uprawnienia:** Dostęp do panelu magazynowego, zarządzanie materiałami, dostawy

---

## Baza Danych

### MySQL (Laragon)
- **Host:** 127.0.0.1
- **Port:** 3306
- **Database:** `okna_produkcja`
- **Username:** `root`
- **Password:** *(puste)*

---

## Uruchomienie Backendu

### Z Laragona (Rekomendowane)
```powershell
cd C:\Users\Admin\Desktop\vueLavarell\backend
C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe artisan serve --host=127.0.0.1 --port=8000
```

### Wersja PHP
- **PHP 8.3.28** (z Laragona)
- Ścieżka: `C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe`

---

## Uruchomienie Frontendu

```powershell
cd C:\Users\Admin\Desktop\vueLavarell\frontend
npm run dev
```

Frontend automatycznie połączy się z backendem przez proxy Vite (konfiguracja w `vite.config.js`).

---

## Seedowanie Bazy Danych

### Tworzenie użytkowników
```powershell
cd C:\Users\Admin\Desktop\vueLavarell\backend
C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe artisan db:seed --class=UsersTableSeeder
```

### Pełne seedowanie
```powershell
C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe artisan db:seed
```

---

## API Endpoints (Przykłady)

### Login
```http
POST http://127.0.0.1:8000/api/login
Content-Type: application/json

{
  "email": "admin@windowwidow.pl",
  "password": "admin123"
}
```

### Pobierz zlecenia produkcyjne
```http
GET http://127.0.0.1:8000/api/production/orders
Authorization: Bearer {token}
```

### Zgłoś problem
```http
POST http://127.0.0.1:8000/api/production/orders/{id}/report-issue
Authorization: Bearer {token}
Content-Type: application/json

{
  "issue_type": "material_shortage",
  "severity": "high",
  "description": "Brak profili aluminiowych",
  "impact": "major_delay",
  "estimated_delay_hours": 8
}
```

---

## Ważne Konfiguracje

### SESSION_DRIVER
Backend używa `cookie` jako session driver (ustawione w `.env`):
```env
SESSION_DRIVER=cookie
```

### API URLs
Wszystkie serwisy frontendowe używają relative URL `/api` z proxy Vite:
- `productionApi.js`: `API_BASE = '/api'`
- `warehouseApi.js`: `API_BASE = '/api'`
- `notificationApi.js`: `API_URL = '/api'`
- `api.js`: `baseURL = '/api'`

### CORS
CORS skonfigurowany dla frontendu w `config/cors.php`:
```php
'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:5173')]
```

---

## Status Funkcjonalności

✅ **Działające:**
- Login i autentykacja (Sanctum tokens)
- Routing i guards (role-based access)
- API interceptor (automatyczne przekierowanie na /login przy 401)
- Production Orders API
- Warehouse API
- Notifications API
- Report Issue (z poprawnymi wartościami enum)
- Update Order Status

✅ **Naprawione problemy:**
- Wartości enum zgodne z migracją (quality_issue, no_delay/minor_delay/major_delay/blocking)
- SESSION_DRIVER ustawiony na cookie
- API URLs używają proxy Vite
- Pole `reported_by` automatycznie uzupełniane z zalogowanego użytkownika

---

## Kontakt Developer

Dla pytań technicznych lub problemów kontaktuj się z zespołem development.

**Ostatnia aktualizacja:** 8 stycznia 2026

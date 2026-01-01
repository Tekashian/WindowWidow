# System Zarządzania Produkcją Okien

Profesjonalny system do zarządzania produkcją okien dla firmy produkcyjnej.

## 🏗️ Architektura

- **Backend**: Laravel 10.x (PHP 8.1+) - REST API
- **Frontend**: Vue.js 3 + Vite
- **Baza danych**: MySQL
- **Styl API**: RESTful

## 📂 Struktura projektu

```
vueLavarell/
├── backend/                    # Laravel API
│   ├── app/
│   │   ├── Http/
│   │   │   └── Controllers/
│   │   │       └── Api/       # Kontrolery API
│   │   └── Models/            # Modele Eloquent
│   ├── database/
│   │   └── migrations/        # Migracje bazy danych
│   ├── routes/
│   │   └── api.php           # Definicje routingu API
│   ├── config/
│   │   └── cors.php          # Konfiguracja CORS
│   └── composer.json
│
├── frontend/                  # Vue.js Frontend
│   ├── src/
│   │   ├── components/       # Komponenty Vue
│   │   ├── views/            # Widoki/strony
│   │   ├── router/           # Vue Router
│   │   ├── services/         # Serwisy API
│   │   └── stores/           # Pinia stores
│   ├── package.json
│   └── vite.config.js
│
└── .github/
    └── copilot-instructions.md
```

## 🚀 Szybki start

### Wymagania

- PHP >= 8.1
- Composer
- Node.js >= 18.x
- npm lub yarn
- MySQL >= 8.0

### Instalacja Backend (Laravel)

1. Przejdź do katalogu backend:
```bash
cd backend
```

2. Zainstaluj zależności PHP:
```bash
composer install
```

3. Skopiuj plik konfiguracyjny:
```bash
cp .env.example .env
```

4. Wygeneruj klucz aplikacji:
```bash
php artisan key:generate
```

5. Skonfiguruj bazę danych w pliku `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=window_factory
DB_USERNAME=root
DB_PASSWORD=twoje_haslo
```

6. Utwórz bazę danych:
```bash
mysql -u root -p -e "CREATE DATABASE window_factory CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

7. Uruchom migracje:
```bash
php artisan migrate
```

8. Uruchom serwer deweloperski:
```bash
php artisan serve
```

API będzie dostępne pod adresem: `http://localhost:8000`

### Instalacja Frontend (Vue.js)

1. Przejdź do katalogu frontend:
```bash
cd frontend
```

2. Zainstaluj zależności Node.js:
```bash
npm install
```

3. Uruchom serwer deweloperski:
```bash
npm run dev
```

Frontend będzie dostępny pod adresem: `http://localhost:5173`

## 📡 API Endpoints

### Windows (Okna)
- `GET /api/windows` - Lista wszystkich okien
- `GET /api/windows/{id}` - Szczegóły okna
- `POST /api/windows` - Dodaj nowe okno
- `PUT /api/windows/{id}` - Aktualizuj okno
- `DELETE /api/windows/{id}` - Usuń okno

### Profiles (Profile)
- `GET /api/profiles` - Lista profili
- `GET /api/profiles/{id}` - Szczegóły profilu
- `POST /api/profiles` - Dodaj profil
- `PUT /api/profiles/{id}` - Aktualizuj profil
- `DELETE /api/profiles/{id}` - Usuń profil

### Glasses (Szkła)
- `GET /api/glasses` - Lista szkieł
- `GET /api/glasses/{id}` - Szczegóły szkła
- `POST /api/glasses` - Dodaj szkło
- `PUT /api/glasses/{id}` - Aktualizuj szkło
- `DELETE /api/glasses/{id}` - Usuń szkło

### Orders (Zamówienia)
- `GET /api/orders` - Lista zamówień
- `GET /api/orders/{id}` - Szczegóły zamówienia
- `POST /api/orders` - Utwórz zamówienie
- `PUT /api/orders/{id}` - Aktualizuj zamówienie
- `POST /api/orders/{id}/update-status` - Zmień status zamówienia
- `DELETE /api/orders/{id}` - Usuń zamówienie

### Health Check
- `GET /api/health` - Sprawdzenie stanu API

## 🗄️ Schemat bazy danych

### Tabele

#### `profiles` - Profile okienne
- id, name, manufacturer, type, material, color, price_per_meter, is_active

#### `glasses` - Typy szkła
- id, name, type, thickness, u_value, price_per_sqm, description, is_active

#### `windows` - Okna
- id, name, type, width, height, profile_id, glass_id, price, description, is_active

#### `orders` - Zamówienia
- id, order_number, customer_name, customer_email, customer_phone, delivery_address, status, total_price, notes, ordered_at, completed_at

#### `order_items` - Pozycje zamówienia
- id, order_id, window_id, quantity, unit_price, total_price

## 🔧 Konfiguracja

### CORS

Konfiguracja CORS znajduje się w pliku `backend/config/cors.php`. Domyślnie API akceptuje requesty z `http://localhost:5173`.

### Środowisko

Backend (`.env`):
```env
FRONTEND_URL=http://localhost:5173
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:5173
```

Frontend (`.env`):
```env
VITE_API_URL=http://localhost:8000/api
```

## 📦 Budowanie dla produkcji

### Backend
```bash
cd backend
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Frontend
```bash
cd frontend
npm run build
```

Zbudowane pliki znajdą się w katalogu `frontend/dist/`

## 🛠️ Technologie

### Backend
- **Laravel 10.x** - Framework PHP
- **Eloquent ORM** - Obsługa bazy danych
- **Laravel Sanctum** - Autoryzacja (gotowe do implementacji)
- **CORS** - Obsługa cross-origin requests

### Frontend
- **Vue.js 3** - Framework JavaScript
- **Vue Router** - Routing
- **Pinia** - State management (gotowe do użycia)
- **Axios** - HTTP client
- **Vite** - Build tool

## 📋 Funkcjonalności

✅ Zarządzanie produktami (okna, profile, szkła)  
✅ System zamówień z kalkulacją cen  
✅ Śledzenie statusów zamówień  
✅ REST API z pełnym CRUD  
✅ Responsive design  
✅ Walidacja danych  
✅ Obsługa błędów  

## 🔐 Bezpieczeństwo

- Walidacja danych po stronie backendu
- Przygotowane do implementacji autoryzacji (Laravel Sanctum)
- CORS skonfigurowany
- Prepared statements (Eloquent ORM)

## 🧪 Testowanie

### Backend
```bash
cd backend
php artisan test
```

### Frontend
```bash
cd frontend
npm run test
```

## 📝 Standardy kodowania

### PHP (Backend)
- PSR-12 coding standard
- PHP 8.1+ features
- Type hints
- Return types

### JavaScript/Vue (Frontend)
- ESLint + Prettier
- Composition API
- Script setup
- TypeScript ready

## 🤝 Wsparcie

W razie problemów lub pytań, sprawdź:
- Logi Laravel: `backend/storage/logs/laravel.log`
- Konsola przeglądarki (DevTools)
- Network tab dla requestów API

## 📄 Licencja

Proprietary - Własność firmy

## 👨‍💻 Autorzy

Projekt stworzony dla firmy produkującej okna.

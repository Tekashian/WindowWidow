# 🚀 Przewodnik Instalacji - System Zarządzania Produkcją Okien

## Wymagania systemowe

- **PHP** >= 8.1
- **Composer** >= 2.5
- **Node.js** >= 18.x
- **MySQL** >= 8.0
- **Git**

---

## 📦 Instalacja Backend (Laravel)

### Krok 1: Instalacja zależności

```powershell
cd backend
composer install
```

### Krok 2: Konfiguracja środowiska

Skopiuj plik `.env.example` do `.env`:

```powershell
Copy-Item .env.example .env
```

### Krok 3: Edytuj `.env` - ustaw dane bazy danych

Otwórz plik `backend/.env` i ustaw:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=okna_produkcja
DB_USERNAME=root
DB_PASSWORD=
```

### Krok 4: Utwórz bazę danych

W MySQL wykonaj:

```sql
CREATE DATABASE okna_produkcja CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Lub przez PowerShell (jeśli masz mysql w PATH):

```powershell
mysql -u root -p -e "CREATE DATABASE okna_produkcja CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### Krok 5: Wygeneruj klucz aplikacji

```powershell
php artisan key:generate
```

### Krok 6: Uruchom migracje i seedy (dane testowe)

```powershell
php artisan migrate:fresh --seed
```

To stworzy:
- ✅ Wszystkie tabele (users, windows, materials, production_orders, itd.)
- ✅ 3 użytkowników testowych
- ✅ Przykładowe produkty (okna, profile, szkła)
- ✅ Materiały magazynowe (z jednym niskim stanem)

### Krok 7: Uruchom serwer Laravel

```powershell
php artisan serve
```

Backend działa na: **http://localhost:8000** ✅

---

## 🎨 Instalacja Frontend (Vue.js)

### Krok 1: Instalacja zależności

Otwórz **NOWE** okno PowerShell i przejdź do folderu frontend:

```powershell
cd frontend
npm install
```

### Krok 2: Uruchom serwer deweloperski

```powershell
npm run dev
```

Frontend działa na: **http://localhost:5173** ✅

---

## 🔐 Logowanie - Konta testowe

Po uruchomieniu obu serwerów, otwórz przeglądarkę i wejdź na:

👉 **http://localhost:5173**

### Dostępni użytkownicy:

| Email | Hasło | Rola | Uprawnienia |
|-------|-------|------|-------------|
| `admin@okna.pl` | `admin123` | Administrator | Pełny dostęp do wszystkiego |
| `magazyn@okna.pl` | `magazyn123` | Magazynier | Zarządzanie stanami magazynowymi |
| `produkcja@okna.pl` | `produkcja123` | Pracownik produkcji | Zarządzanie zleceniami |

---

## 🎯 Krótki test funkcjonalności

### 1. Zaloguj się jako ADMIN

```
Email: admin@okna.pl
Hasło: admin123
```

### 2. Przejdź do Dashboard
- Zobaczysz statystyki: zlecenia, okna, materiały
- Alerty niskiego stanu magazynowego (uszczelka EPDM: 15/50 mb)

### 3. Magazyn → Dodaj/Usuń materiał
- Kliknij `➕` lub `➖` na karcie materiału
- Zobacz historię ruchów magazynowych

### 4. Zlecenia Produkcyjne → Nowe zlecenie
- Wybierz okno z listy
- Ustaw priorytet
- System **automatycznie** sprawdzi dostępność materiałów
- Po rozpoczęciu produkcji materiały zostaną pobrane z magazynu

### 5. Produkty → Dodaj okno
- Uzupełnij dane (nazwa, typ, wymiary)
- Wybierz profil i szkło
- Status domyślnie: "Projekt"

---

## 🛠️ Przydatne komendy

### Backend (Laravel)

```powershell
# Restart bazy z nowymi danymi
php artisan migrate:fresh --seed

# Cache config (produkcja)
php artisan config:cache

# Sprawdź routes
php artisan route:list

# Stwórz nowego użytkownika przez tinker
php artisan tinker
# >>> User::create(['name'=>'Jan', 'email'=>'jan@test.pl', 'password'=>bcrypt('pass123'), 'role'=>'admin'])
```

### Frontend (Vue.js)

```powershell
# Build produkcyjny
npm run build

# Podgląd builda
npm run preview

# Linting
npm run lint
```

---

## 📊 Struktura bazy danych

Po seederze masz:

### Użytkownicy (3)
- Admin, Magazynier, Pracownik produkcji

### Produkty (9)
- 3 okna (różne typy)
- 3 profile (VEKA, Aluplast, Reynaers)
- 3 szkła (4mm, 6mm, pakiet 24mm)

### Materiały (8)
- 3 profile różnych kolorów
- 2 szkła (float, pakiet)
- Okucia, uszczelka EPDM (**⚠️ niski stan!**), silikon

### Zlecenia (0)
- Stwórz pierwsze przez interfejs

---

## ❌ Troubleshooting

### Problem: `composer: command not found`
**Rozwiązanie:** Zainstaluj Composer z https://getcomposer.org/download/

### Problem: `php: command not found`
**Rozwiązanie:** Zainstaluj PHP lub dodaj do PATH. Polecam XAMPP/Laragon.

### Problem: `npm: command not found`
**Rozwiązanie:** Zainstaluj Node.js z https://nodejs.org/

### Problem: `SQLSTATE[HY000] [2002] Connection refused`
**Rozwiązanie:** 
1. Sprawdź czy MySQL działa: `mysql -V`
2. Sprawdź dane w `.env` (DB_HOST, DB_USERNAME, DB_PASSWORD)
3. Uruchom MySQL server

### Problem: `419 CSRF Token Mismatch`
**Rozwiązanie:**
```powershell
php artisan config:clear
php artisan cache:clear
```

### Problem: Frontend nie łączy się z API
**Rozwiązanie:**
1. Sprawdź czy Laravel działa na `http://localhost:8000`
2. Sprawdź plik `frontend/src/services/api.js` - baseURL powinno być `http://localhost:8000/api`

### Problem: Cors error
**Rozwiązanie:** W `backend/config/cors.php` upewnij się że:
```php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'allowed_origins' => ['http://localhost:5173'],
```

---

## 🎉 Gotowe!

Twój system działa! Możesz teraz:

✅ Zarządzać magazynem  
✅ Tworzyć zlecenia produkcyjne  
✅ Dodawać produkty  
✅ Monitorować statystyki  
✅ Testować autoryzację ról  

---

## 📞 Potrzebujesz pomocy?

Sprawdź logi:
- **Backend:** Terminal gdzie uruchomiłeś `php artisan serve`
- **Frontend:** Terminal gdzie uruchomiłeś `npm run dev`
- **Browser:** F12 → Console (błędy JavaScript)

---

**Autor:** System MVP dla prezentacji umiejętności Full-Stack Developer  
**Stack:** Laravel 10 + Vue.js 3 + Pinia + MySQL  
**Data:** Styczeń 2026

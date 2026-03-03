# Naprawa połączenia z bazą danych - 2026-03-03

## Problem

Przy próbie logowania (`POST /api/login`) aplikacja zwracała błąd 500:
```
SQLSTATE[HY000] [1045] Access denied for user 'root'@'localhost' (using password: YES)
SQLSTATE[HY000] [1049] Unknown database 'okna_produkcja'
```

## Przyczyny (dwie niezależne)

### 1. Błędne hasło w `.env`
- Plik `.env` miał `DB_PASSWORD=Baza123`
- Rzeczywiste hasło root w Laragon MySQL to `Piotrlas1`
- Rozbieżność wynikała z tego, że `CREDENTIALS.md` mówił o pustym haśle, ale MySQL miał ustawione hasło z innego projektu (`find_your_cantor`, `findyourcantor`)

### 2. Brak bazy danych `okna_produkcja`
- Laragon MySQL zawierał tylko bazy innych projektów (`find_your_cantor`, `findyourcantor`)
- Baza `okna_produkcja` nigdy nie została stworzona na tym środowisku
- Brakowało również wszystkich tabel (migracje nie były uruchamiane)

## Co zostało zrobione

1. Poprawiono `backend/.env`:
   ```
   DB_PASSWORD=Piotrlas1
   ```

2. Stworzono bazę danych:
   ```powershell
   & "C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe" -u root -pPiotrlas1 -h 127.0.0.1 -P 3306 -e "CREATE DATABASE okna_produkcja CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   ```

3. Uruchomiono migracje i seed:
   ```powershell
   cd backend
   C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe artisan migrate:fresh --seed
   ```

## Na przyszłość - checklista nowego środowiska

Przy stawianiu projektu od zera na nowej maszynie wykonaj kolejno:

1. **Sprawdź hasło MySQL** - otwórz HeidiSQL w Laragonie i sprawdź zapisane dane połączenia
2. **Ustaw `.env`** - skopiuj `.env.example` i uzupełnij `DB_PASSWORD` swoim hasłem
3. **Stwórz bazę** - jeśli nie istnieje, utwórz ją ręcznie lub przez Laragon → Database
4. **Uruchom migracje** - `php artisan migrate:fresh --seed`
5. **Wyczyść cache** - `php artisan config:clear`

## Konfiguracja środowiska lokalnego

| Parametr | Wartość |
|---|---|
| PHP | `C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe` |
| MySQL | Laragon MySQL 8.4.3, port 3306 |
| DB_HOST | 127.0.0.1 |
| DB_PORT | 3306 |
| DB_DATABASE | okna_produkcja |
| DB_USERNAME | root |
| DB_PASSWORD | Piotrlas1 |

> ⚠️ Nie commituj `.env` z hasłem do repozytorium. Plik `.env` powinien być w `.gitignore`.

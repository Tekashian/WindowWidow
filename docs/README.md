# Dokumentacja Systemu Zarządzania Produkcją Okien

## Struktura dokumentacji

| Plik | Opis |
|------|------|
| [01-architektura.md](01-architektura.md) | Architektura ogólna systemu (Vue ↔ Laravel ↔ MySQL) |
| [02-baza-danych-erd.md](02-baza-danych-erd.md) | Diagram ERD — tabele i powiązania |
| [03-autentykacja.md](03-autentykacja.md) | Przepływ logowania, Sanctum token |
| [04-role-i-dostepy.md](04-role-i-dostepy.md) | RBAC — kto ma dostęp do czego |
| [05-state-machine-produkcja.md](05-state-machine-produkcja.md) | Cykl życia zlecenia produkcyjnego |
| [06-frontend-przepływ-danych.md](06-frontend-przeplyw-danych.md) | Vue 3 + Pinia — jak dane płyną przez UI |
| [07-endpointy-api.md](07-endpointy-api.md) | Pełna lista endpointów REST API |
| [08-sciagawka-prezentacja.md](08-sciagawka-prezentacja.md) | Ściągawka na prezentację u pracodawcy |

## Skróty

- **Backend**: `C:\Users\Admin\Desktop\vueLavarell\backend\` (Laravel 10, port 8000)
- **Frontend**: `C:\Users\Admin\Desktop\vueLavarell\frontend\` (Vue 3 + Vite, port 5173)
- **Baza danych**: MySQL 8.4.3, baza: `okna_produkcja`, port 3306

## Uruchomienie projektu

```powershell
# Terminal 1 — Backend
Set-Location C:\Users\Admin\Desktop\vueLavarell\backend
C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe artisan serve --host=127.0.0.1 --port=8000

# Terminal 2 — Frontend
Set-Location C:\Users\Admin\Desktop\vueLavarell\frontend
npm run dev
```

## Dane testowe (logowanie)

| Rola | Email | Hasło |
|------|-------|-------|
| Admin | admin@windowwidow.pl | admin123 |
| Produkcja | produkcja@windowwidow.pl | prod123 |
| Magazyn | magazyn@windowwidow.pl | mag123 |

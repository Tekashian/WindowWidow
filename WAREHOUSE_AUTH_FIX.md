# 🔍 Diagnoza Problemu: Błąd 500 na Panel Magazynu

## Problem
`GET /api/warehouse/deliveries/statistics` zwraca **500 Internal Server Error**

## Przyczyna
**AuthenticationException** - użytkownik nie jest zalogowany lub token wygasł.

### Szczegóły z logów:
```
Illuminate\Auth\AuthenticationException
Handler->unauthenticated()
```

## Rozwiązanie dla Użytkownika

### ✅ MUSISZ SIĘ ZALOGOWAĆ JAKO MAGAZYNIER:

1. **Wyloguj się** (jeśli jesteś zalogowany)
2. **Zaloguj ponownie** jako:
   - Email: `magazyn@windowwidow.pl`
   - Hasło: `mag123`

### Dlaczego?
- Endpoint `/api/warehouse/deliveries/*` wymaga autentykacji przez **Sanctum token**
- Token jest przechowywany w `localStorage` po zalogowaniu
- Jeśli token wygasł lub nie istnieje → błąd 401/500

## Zmiany Techniczne Wprowadzone

### 1. Dodano Axios Interceptor
**Plik:** `frontend/src/plugins/axios.js`
- Automatycznie dodaje `Authorization: Bearer {token}` do wszystkich requestów
- Obsługuje błędy 401 (redirect do logowania)

### 2. Import w main.js
**Plik:** `frontend/src/main.js`
- Dodano `import './plugins/axios'`

### 3. Zabezpieczono endpoint statistics
**Plik:** `backend/app/Http/Controllers/WarehouseDeliveryController.php`
- Dodano try-catch
- Zwraca puste dane zamiast crashować

## Struktura Autentykacji

```
Request → Frontend
  ↓
localStorage.getItem('authToken')
  ↓
Header: Authorization: Bearer {token}
  ↓
Backend → auth:sanctum middleware
  ↓
RoleMiddleware → sprawdza role (warehouse/admin)
  ↓
WarehouseDeliveryController
```

## Testowanie

1. **Sprawdź localStorage** (DevTools → Application → Local Storage):
   - Czy istnieje klucz `authToken`?
   - Czy istnieje klucz `user`?

2. **Jeśli NIE** → zaloguj się ponownie

3. **Jeśli TAK** ale dalej błąd → token wygasł:
   - Wyloguj się
   - Zaloguj ponownie

## Weryfikacja Poprawności

Po zalogowaniu:
```javascript
// W console przeglądarki:
localStorage.getItem('authToken')  // Powinien zwrócić token
localStorage.getItem('user')        // Powinien zwrócić JSON użytkownika
```

Przykładowy user magazynowy:
```json
{
  "id": 3,
  "name": "Anna Nowak",
  "email": "magazyn@windowwidow.pl",
  "role": "warehouse"
}
```

# Autentykacja — Laravel Sanctum + Vue

## Jak to działa (krótko)

1. Użytkownik wpisuje email + hasło
2. Laravel weryfikuje credentiale i zwraca **token**
3. Frontend zapisuje token w `localStorage` pod kluczem `"token"`
4. **Każdy kolejny request** → Axios interceptor dokłada `Authorization: Bearer <token>`
5. Laravel middleware `auth:sanctum` weryfikuje token przy każdym żądaniu

## Diagram sekwencji logowania

```plantuml
@startuml Authentication_Flow

skinparam backgroundColor #FAFAFA
skinparam defaultFontName Arial
skinparam defaultFontSize 12
skinparam sequenceMessageAlign center
skinparam responseMessageBelowArrow true

skinparam participant {
  BackgroundColor #FFFFFF
  BorderColor #455A64
  BorderThickness 1.5
  FontSize 12
}

skinparam actor {
  BackgroundColor #1565C0
  BorderColor #0D47A1
  FontColor #FFFFFF
  FontStyle bold
}

skinparam database {
  BackgroundColor #FFF8E1
  BorderColor #F9A825
  BorderThickness 2
}

skinparam note {
  BackgroundColor #E8F5E9
  BorderColor #4CAF50
  FontSize 10
}

skinparam arrow {
  Color #37474F
  Thickness 1.5
}

skinparam sequence {
  LifeLineBorderColor #90A4AE
  LifeLineBackgroundColor #ECEFF1
  GroupBorderColor #78909C
  GroupBackgroundColor #ECEFF1
  GroupFontStyle bold
  DividerBackgroundColor #E0E0E0
  DividerBorderColor #9E9E9E
}

actor       "User\n<<browser>>"          as U
participant "LoginView.vue\n<<component>>"  as V  #E3F2FD
participant "authStore\n<<pinia>>"          as AS #E8EAF6
participant "api.js\n<<axios>>"             as AX #E8EAF6
participant "AuthController\n<<laravel>>"   as LA #FFF3E0
database    "MySQL\n<<sanctum>>"            as DB

== Logowanie ==

U  ->  V  : submit(email, password)
activate V

V  ->  AS : login({ email, password })
activate AS

AS ->  AX : POST /api/login
activate AX

AX ->  LA : HTTP POST /api/login
activate LA

LA ->  DB : SELECT * FROM users\nWHERE email = ? LIMIT 1
activate DB
DB --> LA : User record
deactivate DB

LA ->  LA : Hash::check(password, hash)

LA ->  DB : INSERT INTO personal_access_tokens\n(tokenable_id, name, token, ...)
activate DB
DB --> LA : token plaintext
deactivate DB

LA --> AX : 200 OK\n{ user: { id, name, role }, token: "1|abc..." }
deactivate LA

AX --> AS : response.data
deactivate AX

note right of AS
  localStorage.setItem('token', '1|abc...')
  localStorage.setItem('tokenExpiry', now + 30min)  ← FRONTEND-only timeout
  localStorage.setItem('user', JSON.stringify(user))
  
  Backend token expires after 30 DAYS (Sanctum)
  Frontend session expires after 30 MINUTES (client check)
end note

AS --> V  : { success: true }
deactivate AS

V  ->  V  : router.push('/')
deactivate V

== Każde kolejne żądanie ==

group Axios Request Interceptor (automatyczny)
  AX ->  AX : token = localStorage.getItem('token')
  AX ->  AX : config.headers.Authorization = 'Bearer ' + token
end

== Wygaśnięcie sesji frontend (30 min) ==

U  ->  V  : nawiguje do chronionej strony
V  ->  AS : isAuthenticated (computed)
AS ->  AS : Date.now() > parseInt(tokenExpiry) ?
note right of AS : returns false — token expired
AS --> V  : false
V  ->  V  : router.push('/login')
@enduml
```

## Ważna różnica: backend vs frontend expiry

| | Backend (Sanctum) | Frontend (authStore) |
|-|-|-|
| Token żyje przez | **30 dni** | **30 minut** |
| Ustawiane przez | `createToken(..., now()->addDays(30))` | `Date.now() + 30*60*1000` |
| Skutek | Token można użyć przez 30 dni | Przeglądarka wylogowuje po 30 min |

Użytkownik jest wylogowywany przez frontend po 30 minutach, ale jego token Sanctum jest nadal ważny na backendzie przez 30 dni (aż do restartu sesji lub manulanego wylogowania).

## Axios interceptor (api.js)

```javascript
// frontend/src/services/api.js
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token')  // ← klucz "token"
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})
```

## Guard routera (router/index.js)

```javascript
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login')                           // brak tokenu → login
  } else if (to.path === '/login' && authStore.isAuthenticated) {
    next('/')                                // już zalogowany → home
  } else if (to.meta.requiresRole) {
    const userRole = authStore.user?.role
    if (!requiredRoles.includes(userRole)) {
      next('/')                              // zła rola → home
    }
  } else {
    next()
  }
})
```

## Wygaśnięcie tokenu (30 minut)

```javascript
// authStore.js — computed property
const isAuthenticated = computed(() => {
  if (!token.value || !tokenExpiry.value) return false
  const now = Date.now()
  const expiry = parseInt(tokenExpiry.value)
  return now < expiry   // false jeśli minęło 30 min
})
```

Gdy `isAuthenticated` zwraca `false`:  
→ Router guard przekierowuje na `/login`  
→ `authStore.logout()` czyści localStorage

## Wylogowanie

```
POST /api/logout (z Bearer tokenem)
→ Laravel usuwa token z personal_access_tokens
→ Frontend czyści localStorage
→ router.push('/login')
```

## Backend — Handler.php (ważna naprawa!)

```php
// Handler.php — JSON zamiast redirect dla API
protected function unauthenticated($request, AuthenticationException $exception)
{
    return response()->json([
        'message' => 'Unauthenticated. Please log in.',
        'error' => 'unauthenticated'
    ], 401);
}
```

> ⚠️ **Naprawiony bug PHP 8**: Oryginał miał `(Request $request, ...)` — PHP 8 zgłosiło fatal error
> bo typ `Request` jest niezgodny z sygnaturą klasy nadrzędnej (w PHP 7 było tylko warning).
> Fix: usunięcie type hinta `Request`.

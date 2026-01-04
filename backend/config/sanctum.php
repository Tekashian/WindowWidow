<?php

return [
    'stateful' => [],
    'guard' => null,
    'expiration' => 30 * 24 * 60, // 30 dni w minutach
    'middleware' => [
        'verify_csrf_token' => App\Http\Middleware\VerifyCsrfToken::class,
        'encrypt_cookies' => App\Http\Middleware\EncryptCookies::class,
    ],
];

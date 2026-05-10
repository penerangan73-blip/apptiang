<?php

return [
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', '127.0.0.1:3000,localhost:3000')),
    'guard' => ['web'],
    'expiration' => null,
    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),
    'middleware' => [
        'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
        'encrypt_cookies' => Illuminate\Cookie\Middleware\EncryptCookies::class,
        'validate_csrf_token' => Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    ],
    'personal_access_token_model' => Laravel\Sanctum\PersonalAccessToken::class,
];

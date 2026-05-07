<?php

return [
    'paths' => ['api/*', 'storage/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        env('FRONTEND_URL', 'http://127.0.0.1:3000'),
        'http://127.0.0.1:3000',
        'http://localhost:3000',
        'http://192.168.1.16:3000',
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];

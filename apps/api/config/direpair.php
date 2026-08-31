<?php

return [
    'frontend_url' => env('FRONTEND_URL', 'http://localhost:4321'),
    'public_status_url' => env('PUBLIC_STATUS_URL', 'http://localhost:4321/cek-status/'),
    'payment_driver' => env('PAYMENT_DRIVER', 'mock'),
    'token_pepper' => env('STATUS_TOKEN_PEPPER') ?: env('APP_KEY'),
    'privacy_policy_version' => env('PRIVACY_POLICY_VERSION', 'draft-local'),
    'demo_admin' => [
        'email' => env('DEMO_ADMIN_EMAIL', 'admin@direpair.test'),
        'password' => env('DEMO_ADMIN_PASSWORD', 'direpair-local-only'),
    ],
];

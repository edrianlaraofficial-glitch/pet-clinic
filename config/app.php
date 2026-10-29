<?php

return [
    'name' => env('APP_NAME', 'Happy Tails Dog Clinic'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'require_admin_email_verification' => env('APP_ENV', 'production') !== 'local',
    'timezone' => env('APP_TIMEZONE', 'Asia/Manila'),
    'locale' => env('APP_LOCALE', 'en'),
    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),
    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),
    'cipher' => 'AES-256-CBC',
    'key' => env('APP_KEY'),
    'previous_keys' => [],
    'admin_seed' => [
        'email' => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
    ],
    'admin_setup_key' => env('ADMIN_SETUP_KEY'),
    'maintenance' => [
        'driver' => 'file',
    ],
];

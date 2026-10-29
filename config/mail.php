<?php

return [
    'default' => env('MAIL_MAILER', 'brevo'),

    'mailers' => [
        'brevo' => [
            'transport' => 'brevo',
            'url' => env('BREVO_DSN'),
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],
    ],

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'edrianlara.official@gmail.com'),
        'name' => env('MAIL_FROM_NAME', 'Wade-County Animal Clinic'),
    ],
];

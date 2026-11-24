<?php

declare(strict_types=1);

return [
    'default' => env('DB_CONNECTION', 'mysql'),
    'connections' => [
        'mysql' => [
            'host' => env('DB_HOST', 'localhost'),
            'database' => env('DB_NAME', 'air-mvc'),
            'username' => env('DB_USER', 'root'),
            'password' => env('DB_PASS', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        ],
    ],
];

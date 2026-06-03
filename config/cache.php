<?php

return [
    'default' => env('CACHE_DRIVER', 'redis'),
    'stores' => [
        'redis' => [
            'driver' => 'redis',
            'connection' => 'cache',
        ],
        'database' => [
            'driver' => 'database',
            'table' => 'cache',
        ],
    ],
    'prefix' => env('CACHE_PREFIX', 'laravel_cache_'),
];

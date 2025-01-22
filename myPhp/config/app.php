<?php

return [
    'name' => getenv('APP_NAME', 'LightPHP'),
    'env' => getenv('APP_ENV', 'production'),
    'debug' => getenv('APP_DEBUG', false),
    'url' => getenv('APP_URL', 'http://localhost'),
    
    'providers' => [
        \Core\Providers\DatabaseServiceProvider::class,
        \Core\Providers\RouteServiceProvider::class,
        \Core\Providers\ViewServiceProvider::class,
    ],
    
    'middleware' => [
        'global' => [
            \App\Middleware\TrimStrings::class,
            \App\Middleware\SecurityHeaders::class,
        ],
        'web' => [
            \App\Middleware\VerifyCsrfToken::class,
            \App\Middleware\StartSession::class,
        ],
        'api' => [
            \App\Middleware\JsonResponse::class,
        ],
    ]
]; 
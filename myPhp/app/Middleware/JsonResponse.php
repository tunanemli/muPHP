<?php

namespace App\Middleware;

class JsonResponse implements Middleware
{
    public function handle($request, callable $next)
    {
        header('Content-Type: application/json');
        return $next($request);
    }
} 
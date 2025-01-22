<?php

namespace App\Middleware;

class VerifyCsrfToken implements Middleware
{
    public function handle($request, callable $next)
    {
        // CSRF verification logic here
        return $next($request);
    }
} 
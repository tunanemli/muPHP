<?php

namespace App\Middleware;

class SecurityHeaders implements Middleware
{
    public function handle($request, callable $next)
    {
        // Security headers logic here
        return $next($request);
    }
} 
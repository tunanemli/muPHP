<?php

namespace App\Middleware;

class TrimStrings implements Middleware
{
    public function handle($request, callable $next)
    {
        // Trim string logic here
        return $next($request);
    }
} 
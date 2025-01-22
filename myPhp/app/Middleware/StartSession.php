<?php

namespace App\Middleware;

class StartSession implements Middleware
{
    public function handle($request, callable $next)
    {
        // Session start logic here
        return $next($request);
    }
} 
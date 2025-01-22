<?php

namespace Core\Providers;

abstract class ServiceProvider
{
    abstract public function register();
    abstract public function boot();
} 
<?php

namespace Core\View;

class View
{
    protected static ?Engine $engine = null;
    
    public static function make(string $view, array $data = []): string
    {
        return static::engine()->render($view, $data);
    }
    
    public static function engine(): Engine
    {
        if (static::$engine === null) {
            static::$engine = new Engine(storage_path('framework/views'));
        }
        
        return static::$engine;
    }
    
    public static function directive(string $name, callable $handler): void
    {
        static::engine()->directive($name, $handler);
    }
} 
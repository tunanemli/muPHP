<?php

namespace Core;

class Router
{
    private static $instance = null;
    private $routes = [];
    
    private function __construct() {}
    
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function get($path, $handler)
    {
        $this->routes['GET'][$path] = $handler;
    }
    
    public function post($path, $handler)
    {
        $this->routes['POST'][$path] = $handler;
    }
    
    public function put($path, $handler)
    {
        $this->routes['PUT'][$path] = $handler;
    }
    
    public function delete($path, $handler)
    {
        $this->routes['DELETE'][$path] = $handler;
    }
    
    public function group(array $attributes, callable $callback)
    {
        $callback($this);
    }
} 
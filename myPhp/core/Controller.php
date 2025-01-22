<?php

namespace Core;

abstract class Controller
{
    protected function view(string $name, array $data = [])
    {
        // Basic view rendering logic
        $viewPath = __DIR__ . '/../resources/views/' . str_replace('.', '/', $name) . '.php';
        
        if (file_exists($viewPath)) {
            extract($data);
            ob_start();
            require $viewPath;
            return ob_get_clean();
        }
        
        throw new \RuntimeException("View {$name} not found.");
    }
} 
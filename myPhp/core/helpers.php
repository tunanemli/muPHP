<?php

if (!function_exists('storage_path')) {
    function storage_path(string $path = ''): string
    {
        return rtrim(dirname(__DIR__) . '/storage/' . $path, '/');
    }
}

if (!function_exists('resource_path')) {
    function resource_path(string $path = ''): string
    {
        return rtrim(dirname(__DIR__) . '/resources/' . $path, '/');
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return $_SESSION['_token'] ?? '';
    }
} 
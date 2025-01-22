<?php

if (!function_exists('view')) {
    function view(string $view, array $data = []): string
    {
        return \Core\View\View::make($view, $data);
    }
}

if (!function_exists('e')) {
    function e($value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false);
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
    }
}

if (!function_exists('method_field')) {
    function method_field(string $method): string
    {
        return '<input type="hidden" name="_method" value="' . $method . '">';
    }
} 
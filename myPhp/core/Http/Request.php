<?php

namespace Core\Http;

class Request
{
    private $get;
    private $post;
    private $server;
    private $files;
    private $cookies;

    public function __construct()
    {
        $this->get = $_GET;
        $this->post = $_POST;
        $this->server = $_SERVER;
        $this->files = $_FILES;
        $this->cookies = $_COOKIE;
    }

    public function getMethod(): string
    {
        return $this->server['REQUEST_METHOD'];
    }

    public function getUri(): string
    {
        return parse_url($this->server['REQUEST_URI'], PHP_URL_PATH);
    }

    public function input(string $key, $default = null)
    {
        return $this->post[$key] ?? $this->get[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->get, $this->post);
    }

    public function has(string $key): bool
    {
        return isset($this->post[$key]) || isset($this->get[$key]);
    }

    public function file(string $key)
    {
        return $this->files[$key] ?? null;
    }
} 
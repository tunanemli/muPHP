<?php

namespace Core\Http;

class Response
{
    private $content;
    private $statusCode;
    private $headers;

    public function __construct($content = '', int $statusCode = 200, array $headers = [])
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = array_merge([
            'Content-Type' => 'text/html; charset=UTF-8'
        ], $headers);
    }

    public function send(): void
    {
        $this->sendHeaders();
        $this->sendContent();
    }

    private function sendHeaders(): void
    {
        http_response_code($this->statusCode);
        
        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }
    }

    private function sendContent(): void
    {
        echo $this->content;
    }

    public function json($data): self
    {
        $this->headers['Content-Type'] = 'application/json';
        $this->content = json_encode($data);
        return $this;
    }

    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;
        return $this;
    }
} 
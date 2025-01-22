<?php

namespace Core;

class Framework
{
    private static $instance = null;
    
    private function __construct() {}
    
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function run()
    {
        // Application logic here
    }
} 
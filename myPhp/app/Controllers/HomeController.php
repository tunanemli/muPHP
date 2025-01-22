<?php

namespace App\Controllers;

use Core\Controller;

class HomeController extends Controller
{
    public function index()
    {
        return $this->view('home.index', [
            'title' => 'Welcome to LightPHP'
        ]);
    }
} 
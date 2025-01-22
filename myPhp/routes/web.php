<?php

use Core\Router;

$router = Router::getInstance();

// Ana sayfa rotası
$router->get('/', 'HomeController@index');

// Kullanıcı rotaları
$router->group(['prefix' => 'users', 'middleware' => ['auth']], function($router) {
    $router->get('/', 'UserController@index');
    $router->get('/create', 'UserController@create');
    $router->post('/', 'UserController@store');
    $router->get('/{id}', 'UserController@show');
    $router->get('/{id}/edit', 'UserController@edit');
    $router->put('/{id}', 'UserController@update');
    $router->delete('/{id}', 'UserController@destroy');
}); 
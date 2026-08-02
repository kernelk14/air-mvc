<?php

$router = new Router();

$router->get('/', 'HomeController::index');
$router->get('/user/{id}', 'HomeController::show');
$router->get('/login', 'LoginController::index');

$router->post('/click', 'HomeController::clickBtn');
<?php

$router = new Router();

$router->get('/', 'HomeController::index');
$router->post('/click', 'HomeController::clickBtn');
$router->get('/user/{id}', 'HomeController::show');
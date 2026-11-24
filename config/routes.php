<?php

declare(strict_types=1);

use Air\Routing\Router;
use App\Controllers\HomeController;
use App\Controllers\LoginController;

/** @var Router $router */

$router->get('/', [HomeController::class, 'index'])->name('home');
$router->get('/user/{id}', [HomeController::class, 'show'])->name('user.show');
$router->get('/login', [LoginController::class, 'index'])->name('login');
$router->post('/click', [HomeController::class, 'clickBtn'])->name('click')->middleware('csrf');

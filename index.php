<?php
// Thank you AI (duck.ai) for helping me with this one.
define('APP_DIR', __DIR__ . '/app/');
define('CONFIG_DIR', __DIR__ . '/config/');
define('CORE_DIR', __DIR__ . '/core/');
define('ASSETS_DIR', __DIR__ . '/assets/');

spl_autoload_register(function ($className) {
    $controllerFile = APP_DIR . 'controllers/' . $className . '.php';
    $modelFile = APP_DIR . 'models/' . $className . '.php';
    $coreFile = CORE_DIR . '/' . $className . '.php';
    
    if (file_exists($controllerFile)) require_once $controllerFile; 
    if (file_exists($modelFile)) require_once $modelFile;
    if (file_exists($coreFile)) require_once $coreFile; 
});

require_once CONFIG_DIR . 'Routes.php';
$router->dispatch();
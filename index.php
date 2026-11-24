<?php

declare(strict_types=1);

if (PHP_SAPI === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $file = __DIR__ . (is_string($path) ? $path : '/');
    if (is_file($file)) {
        return false;
    }
}

use Air\Http\Request;

require __DIR__ . '/vendor/autoload.php';

/** @var \Air\Application $app */
$app = require __DIR__ . '/bootstrap/app.php';

/** @var \Air\Http\Kernel $kernel */
$kernel = $app->make(\Air\Http\Kernel::class);

$request = Request::capture();
$response = $kernel->handle($request);
$response->send();

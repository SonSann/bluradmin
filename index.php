<?php
if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

require __DIR__ . '/vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

session_start();

// Instantiate the app
$settings = require __DIR__ . '/server/src/settings.php';
$app = new \Slim\App($settings);

// Set up dependencies
require __DIR__ . '/server/src/dependencies.php';

// Register middleware
require __DIR__ . '/server/src/middleware.php';

// Register routes
require __DIR__ . '/server/src/routes.php';

// Run app
$app->run();
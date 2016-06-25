<?php

use Dotenv\Dotenv;
use Rox\Core\Kernel\Application;
use Symfony\Component\HttpFoundation\Request;

/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));

// Decline static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server'
    && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

// Setup autoloading
require 'vendor/autoload.php';

$dotEnv = new Dotenv('.');

$dotEnv->load();

$request = Request::createFromGlobals();

$app = new Application(getenv('APP_ENV'), (getenv('APP_DEBUG') === 'true'));

$response = $app->handle($request);

$response->send();

$app->terminate($request, $response);

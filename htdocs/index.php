<?php

/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
use Dotenv\Dotenv;
use Rox\Core\Kernel\Application;
use Symfony\Component\HttpFoundation\Request;

chdir(dirname(__DIR__));

// Decline static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server'
    && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

// Setup autoloading
require 'vendor/autoload.php';

$cached = 'vendor/bootstrap.php';

if (file_exists($cached)) {
    require_once $cached;
}

$dotEnv = new Dotenv('.');

$dotEnv->load();

$request = Request::createFromGlobals();

// TODO review Symfony best practices for setting language, also when it comes from a user setting
// http://symfony.com/doc/current/cookbook/session/locale_sticky_session.html
$lang = Locale::getPrimaryLanguage($request->getPreferredLanguage());

$request->setLocale($lang);

$app = new Application(getenv('APP_ENV'), (getenv('APP_DEBUG') === 'true'));

$response = $app->handle($request);

$response->send();

$app->terminate($request, $response);

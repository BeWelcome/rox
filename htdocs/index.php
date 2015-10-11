<?php

use Symfony\Component\EventDispatcher\EventDispatcher;

function FriendlyErrorType($type)
{
    switch($type)
    {
        case E_ERROR: // 1 //
            return 'E_ERROR';
        case E_WARNING: // 2 //
            return 'E_WARNING';
        case E_PARSE: // 4 //
            return 'E_PARSE';
        case E_NOTICE: // 8 //
            return 'E_NOTICE';
        case E_CORE_ERROR: // 16 //
            return 'E_CORE_ERROR';
        case E_CORE_WARNING: // 32 //
            return 'E_CORE_WARNING';
        case E_COMPILE_ERROR: // 64 //
            return 'E_COMPILE_ERROR';
        case E_COMPILE_WARNING: // 128 //
            return 'E_COMPILE_WARNING';
        case E_USER_ERROR: // 256 //
            return 'E_USER_ERROR';
        case E_USER_WARNING: // 512 //
            return 'E_USER_WARNING';
        case E_USER_NOTICE: // 1024 //
            return 'E_USER_NOTICE';
        case E_STRICT: // 2048 //
            return 'E_STRICT';
        case E_RECOVERABLE_ERROR: // 4096 //
            return 'E_RECOVERABLE_ERROR';
        case E_DEPRECATED: // 8192 //
            return 'E_DEPRECATED';
        case E_USER_DEPRECATED: // 16384 //
            return 'E_USER_DEPRECATED';
    }
    return "UNKNOWN (" . $type . ")";
}

function catch_errors()
{
    // Getting Last Error
    $lastError =  error_get_last();

    if(isset($lastError['type'])) {

        $errorMessage = "
                Type: " . FriendlyErrorType($lastError['type']) . "<br>
                Message: " . $lastError['message'] . "<br>
                File: " . $lastError['file'] . "<br>
                Line: " . $lastError['line'] . "<br>";
        switch ($lastError['type']) {
            case E_ERROR:
                $response = new Symfony\Component\HttpFoundation\Response($errorMessage);
                $response->send();
                break;
            default:
                error_log($errorMessage);
        }
    }
}

register_shutdown_function('catch_errors');

/**
 *
 * Define directories where the scripts and index.php reside
 */

$script_base = dirname(__FILE__) . "/../";
define('SCRIPT_BASE', $script_base);
define('HTDOCS_BASE', dirname(__FILE__).'/');

ini_set('display_errors', 1);
ini_set('allow_url_fopen', 1);

ini_set('error_log', SCRIPT_BASE . 'errors.log');

/**
 * Setup autoloader for Composer, Rox namespace, and old Rox autoloading loading
 */

require SCRIPT_BASE . 'vendor/autoload.php';

$environmentExplorer = new EnvironmentExplorer;
$environmentExplorer->initializeGlobalState();

// Setup database connection with Eloquent
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'database'  => 'bewelcome',
    'username'  => 'bewelcome',
    'password'  => 'bewelcome',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);

$capsule->setEventDispatcher(new Dispatcher(new Container));

// Make this Capsule instance available globally via static methods... (optional)
$capsule->setAsGlobal();

// Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
$capsule->bootEloquent();

// Create global router object
$locator = new Symfony\Component\Config\FileLocator(array(__DIR__));
$request = Symfony\Component\HttpFoundation\Request::createFromGlobals();
$context = new Symfony\Component\Routing\RequestContext();

$yamlFileLocator = new Symfony\Component\Routing\Loader\YamlFileLoader($locator);
$router = new Symfony\Component\Routing\Router(
    $yamlFileLocator,
    SCRIPT_BASE . 'routes.yml'
);

$context = new Symfony\Component\Routing\RequestContext();
$matcher = new Symfony\Component\Routing\Matcher\UrlMatcher($router->getRouteCollection(), $context);
$resolver = new Symfony\Component\HttpKernel\Controller\ControllerResolver();

$errorHandler = function (Symfony\Component\Debug\Exception\FlattenException $exception) {
    $msg = 'Something went wrong! ('.$exception->getMessage().')';

    return new Symfony\Component\HttpFoundation\Response($msg, $exception->getStatusCode());
};

$dispatcher = new EventDispatcher();
$dispatcher->addSubscriber(new Symfony\Component\HttpKernel\EventListener\RouterListener($matcher));
// $dispatcher->addSubscriber(new Symfony\Component\HttpKernel\EventListener\ExceptionListener($errorHandler));
$dispatcher->addSubscriber(new Rox\Framework\ControllerResolverListener($router));
$framework = new Rox\Framework($dispatcher, $resolver);

try {
    $response = $framework->handle($request);
    $response->send();
}
catch (Twig_Error $e) {
    echo 'Exception: ' . $e->getMessage();
    echo "\n{$e->getFile()} ({$e->getLine()})";
    exit();
}
catch (Exception $e) {
    require_once SCRIPT_BASE . 'roxlauncher/roxlauncher.php';
    $launcher = new RoxLauncher();
    try {
        $launcher->launch($environmentExplorer);
    }
    catch (PException $e) {
        // XML header is a bad idea in this case,
        // because most likely the application already started with XHTML
        // header('Content-type: application/xml; charset=utf-8');
        echo '<pre>';
        print_r($e);
        echo '</pre>';
        exit();
    } catch (Exception $e) {
        echo 'Exception: ' . $e->getMessage();
        echo "\n{$e->getFile()} ({$e->getLine()})";
        exit();
    }
}

session_write_close();

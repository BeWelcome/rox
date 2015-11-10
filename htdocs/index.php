<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use Rox\Framework\ControllerResolverListener;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Security\Http\Firewall;

function FriendlyErrorType($type)
{
    switch ($type) {
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

    return "UNKNOWN (".$type.")";
}

function catch_errors()
{
    // Getting Last Error
    $lastError = error_get_last();

    if (isset($lastError['type'])) {

        $errorMessage
            = "Type: ".FriendlyErrorType($lastError['type']).PHP_EOL.
            "Message: ".$lastError['message'].PHP_EOL.
            "File: ".$lastError['file'].PHP_EOL.
            "Line: ".$lastError['line'].PHP_EOL;
        switch ($lastError['type']) {
            case E_ERROR:
                $response = new Symfony\Component\HttpFoundation\Response(
                    nl2br($errorMessage)
                );
                $response->send();
                break;
            default:
                error_log($errorMessage);
        }
    }
}

register_shutdown_function('catch_errors');

/**
 * Setup some stuff to be used by old Rox components
 */

$script_base = dirname(__FILE__)."/../";
define('SCRIPT_BASE', $script_base);
define('HTDOCS_BASE', dirname(__FILE__).'/');

/**
 * Configure PHP
 */
ini_set('display_errors', 1);
ini_set('allow_url_fopen', 1);

ini_set('error_log', SCRIPT_BASE.'errors.log');

/**
 * Setup autoloader for Composer, Rox namespace, and old Rox autoloading loading
 */

require SCRIPT_BASE.'vendor/autoload.php';

$environmentExplorer = new EnvironmentExplorer;
$environmentExplorer->initializeGlobalState();

$locator = new Symfony\Component\Config\FileLocator(array(SCRIPT_BASE));
$yamlFileLocator = new Symfony\Component\Routing\Loader\YamlFileLoader(
    $locator
);
$router = new Symfony\Component\Routing\Router(
    $yamlFileLocator,
    SCRIPT_BASE.'routes.yml'
);

$context = new Symfony\Component\Routing\RequestContext();

$matcher = new Symfony\Component\Routing\Matcher\UrlMatcher(
    $router->getRouteCollection(), $context
);

// Setup database connection with Eloquent
$params = parse_ini_file('../rox_local.ini');

$parts = explode('=', $params['dsn']);

$host = substr($parts[1], 0, strpos($parts[1], ';'));
$db = $parts[2];
$user = $params['user'];
$password = $params['password'];

$capsule = new Capsule;
$capsule->addConnection(
    [
        'driver' => 'mysql',
        'host' => $host,
        'database' => $db,
        'username' => $user,
        'password' => $password,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix' => '',
    ]
);
$capsule->bootEloquent();

// Create global router object
$request = Symfony\Component\HttpFoundation\Request::createFromGlobals();
$requestContext = new RequestContext($request);

$resolver = new Symfony\Component\HttpKernel\Controller\ControllerResolver();

$errorHandler = function (
    Symfony\Component\Debug\Exception\FlattenException $exception
) {
    $msg = 'Something went wrong! ('.$exception->getMessage().')';

    return new Symfony\Component\HttpFoundation\Response(
        $msg, $exception->getStatusCode()
    );
};

$dispatcher = new EventDispatcher();

$dispatcher->addSubscriber(
    new RouterListener($matcher, $requestContext)
);
// $dispatcher->addSubscriber(new Symfony\Component\HttpKernel\EventListener\ExceptionListener($errorHandler));
$dispatcher->addSubscriber(
    new ControllerResolverListener($router)
);
$framework = new Rox\Framework($dispatcher, $resolver);

try {
    $response = $framework->handle($request);
    $response->send();
} catch (Twig_Error $e) {
    echo 'Exception: '.$e->getMessage();
    echo "\n{$e->getFile()} ({$e->getLine()})";
    exit();
} catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
    require_once SCRIPT_BASE.'roxlauncher/roxlauncher.php';
    $launcher = new RoxLauncher();
    try {
        $launcher->launch($environmentExplorer);
    } catch (PException $e) {
        // XML header is a bad idea in this case,
        // because most likely the application already started with XHTML
        // header('Content-type: application/xml; charset=utf-8');
        echo '<pre>';
        print_r($e);
        echo '</pre>';
        exit();
    } catch (Exception $e) {
        echo 'Exception: '.$e->getMessage();
        echo "\n{$e->getFile()} ({$e->getLine()})";
        exit();
    }
} catch (Exception $e) {
    $response = new Symfony\Component\HttpFoundation\Response(
        nl2br($e->getMessage())
    );
    $response->send();
}

session_write_close();

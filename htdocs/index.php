<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use Rox\Framework\ControllerResolverListener;
use Rox\Framework\SessionSingleton;
use Symfony\Component\Debug\Debug;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Forms;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Firewall;
use Rox\Framework;
use Symfony\Component\Validator\Validation;

/**
 * Setup some stuff to be used by old Rox components
 */

$script_base = dirname(__FILE__)."/../";
define('SCRIPT_BASE', $script_base);
define('HTDOCS_BASE', dirname(__FILE__).'/');

/**
 * Configure PHP
 */
ini_set('allow_url_fopen', 1);
ini_set( 'display_errors', 1 );
ini_set('error_log', SCRIPT_BASE.'errors.log');
ini_set('error_reporting', E_ALL);

/**
 * Setup autoloader for Composer, Rox namespace, and old Rox autoloading loading
 */

require SCRIPT_BASE.'vendor/autoload.php';

Debug::enable();

$session = SessionSingleton::getSession();
$session->setName('sidTB');
$session->start();

$environmentExplorer = new EnvironmentExplorer();
$environmentExplorer->initializeGlobalState();

$locator = new Symfony\Component\Config\FileLocator(array(SCRIPT_BASE));
$yamlFileLocator = new Symfony\Component\Routing\Loader\YamlFileLoader(
    $locator
);

$params = parse_ini_file('../rox_local.ini');

$validator = Validation::createValidator();

$formFactory = Forms::createFormFactoryBuilder()
    ->addExtension(new HttpFoundationExtension())
    ->addExtension(new ValidatorExtension($validator))
    ->getFormFactory();

$request = Request::createFromGlobals();
$request->setSession($session);
$requestContext = new Symfony\Component\Routing\RequestContext();
$hostname = $params['baseuri'];
$hostname = str_replace('http://', '', $hostname);
$hostname = str_replace('/', '', $hostname);
$requestContext->setHost($hostname);

$router = new Symfony\Component\Routing\Router(
    $yamlFileLocator,
    SCRIPT_BASE . 'routes.yml',
    [],
    $requestContext
);

$routesCollection =

$matcher = new Symfony\Component\Routing\Matcher\UrlMatcher(
    $router->getRouteCollection(), $requestContext
);

$tokenStorage = new \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage();

$parts = explode('=', $params['dsn']);
$host = substr($parts[1], 0, strpos($parts[1], ';'));
$db = $parts[2];
$user = $params['user'];
$password = $params['password'];

// Setup database connection with Eloquent
$capsule = new Capsule();
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => $host,
    'database' => $db,
    'username' => $user,
    'password' => $password,
    'charset' => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix' => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

$requestStack = new RequestStack();

$resolver = new Symfony\Component\HttpKernel\Controller\ControllerResolver();

$dispatcher = new EventDispatcher();

$dispatcher->addSubscriber(
    new RouterListener($matcher, $requestStack)
);

$dispatcher->addSubscriber(
    new ControllerResolverListener($router, $formFactory)
);

$dispatcher->addSubscriber(
    new Framework\InteractiveLoginListener($session)
);

$framework = new Rox\Framework($dispatcher, $resolver, $requestStack);

$firewall = new Rox\Framework\Firewall\RoxFirewall($router, $dispatcher, $tokenStorage);

try {
    $response = $framework->handle($request);
    $response->send();
    $framework->terminate($request, $response);
} catch (Twig_Error $e) {
    echo 'Exception: '.$e->getMessage();
    echo "\n{$e->getFile()} ({$e->getLine()})";
    exit();
} catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
    $pathInfo = $request->getPathInfo();
    if (strstr($pathInfo, '.php') !== false) {
        if (strstr($pathInfo, 'admin/') === false) {
            $response = new Symfony\Component\HttpFoundation\Response(
                'Nope nothing here', 404
            );
            $response->send();
        } else {
            require_once HTDOCS_BASE . 'bw/' . $pathInfo;
        }
    } else {
        require_once SCRIPT_BASE . 'roxlauncher/roxlauncher.php';
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
            echo 'Exception: ' . $e->getMessage();
            echo "\n{$e->getFile()} ({$e->getLine()})";
            exit();
        }
    }
}

<?php

namespace AppBundle\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class LegacyLoader
 * @package AppBundle\Routing
 *
 * @SuppressWarnings(PHPMD)
 * Ignore warnings as class is only used as a bridge to the old code
 */
class LegacyLoader extends Loader
{
    /** @var RouteCollection */
    private $routes;

    private $loaded = false;

    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Do not add the "extra" loader twice');
        }

        $this->routes = new RouteCollection();

        // Handle current directory (difference between cache clear and web access)
        $cwd = getcwd();
        if (strpos($cwd, 'web') === false) {
            $dirfix = '';
        } else {
            $dirfix = '../';
        }

        // Include legacy routes to ensure firewall kicks in
        require_once $dirfix . 'routes.php';

        // Forum urls
        $this->addRoute('forums', 'forums', '', '');
        $this->addRoute('bwforum', 'forums/bwforum', '', '');
        $this->addRoute('forum_thread', '/forums/s{threadId}', '', '');
        $this->addRoute('community', '/community', '', '');
        $this->addRoute('faq', '/faq', '', '');
        $this->addRoute('faq_category', '/faq/{category}', '', '');
        $this->addRoute('about_faq_category', '/aboutaactivi/faq/{category}', '', '');
        $this->addRoute('about', '/about', '', '');
        $this->addRoute('getactive', '/about/getactive', '', '');

//        $this->routes->add('activities', new Route( '/activities/100/edit', [
//            '_controller' => 'rox.legacy_controller:showAction'
//        ]));

        return $this->routes;
    }

    private function addRoute($name, $path, $controller, $action, $ignore = null)
    {
        $ignore;
        $path = preg_replace( '^:(.*?):^', '{\1}', $path);
        $this->routes->add($name, new Route( $path, [
            '_controller' => 'rox.legacy_controller:showAction'
        ]));
    }

    public function supports($resource, $type = null)
    {
        return 'legacy' === $type;
    }
}
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
            throw new \RuntimeException('Do not add the "legacy" loader twice');
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
        $this->addRoute('forums', '/forums', '', '');
        $this->addRoute('forums_new', '/forums/new', '', '');
        $this->addRoute('bwforum', 'forums/bwforum', '', '');
        $this->addRoute('forum_thread', '/forums/s{threadId}', '', '');
        $this->addRoute('community', '/community', '', '');
        $this->addRoute('faq', '/faq', '', '');
        $this->addRoute('about_faq', '/about/faq', '', '');
        $this->addRoute('faq_category', '/faq/{category}', '', '');
        $this->addRoute('about_faq_category', '/about/faq/{category}', '', '');
        $this->addRoute('about', '/about', '', '');
        $this->addRoute('stats', '/stats', '', '');
        $this->addRoute('stats_images', '/stats/{image}.png', '', '');
        $this->addRoute('getactive', '/about/getactive', '', '');
        $this->addRoute( 'contactus', '/about/feedback', '', '');
        $this->addRoute('signup', '/signup/', '', '');
        $this->addRoute('signup_1', '/signup/1', '', '');
        $this->addRoute('signup_2', '/signup/2', '', '');
        $this->addRoute('signup_3', '/signup/3', '', '');
        $this->addRoute('signup_4', '/signup/4', '', '');
        $this->addRoute('signup_finish', '/signup/finish', '', '');


        return $this->routes;
    }

    private function addRoute($name, $path, $controller, $action, $ignore = null)
    {
        $ignore;
        $path = preg_replace( '^:(.*?):^', '{\1}', $path);
        $this->routes->add($name, new Route( $path, [
                '_controller' => 'rox.legacy_controller:showAction'
            ], [], [], '', [], ['get', 'post']
            )
        );
    }

    public function supports($resource, $type = null)
    {
        return 'legacy' === $type;
    }
}
<?php

namespace App\Routing;

use RuntimeException;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class LegacyLoader.
 *
 * @SuppressWarnings("PHPMD")
 * Ignore warnings as class is only used as a bridge to the old code
 */
class LegacyLoader extends Loader
{
    /** @var RouteCollection */
    private $routes;

    /** @var bool */
    private $loaded = false;

    /**
     * @throws RuntimeException
     *
     * @return RouteCollection
     */
    public function load(mixed $resource, ?string $type = null): mixed
    {
        if (true === $this->loaded) {
            throw new RuntimeException('Do not add the "legacy" loader twice');
        }

        $this->routes = new RouteCollection();

        // Handle current directory (difference between cache clear and web access)
        $projectDir = realpath(__DIR__ . '/../..');

        // Include legacy routes to ensure firewall kicks in. require, not require_once: the route cache can be
        // rebuilt in a process that loaded the file before, and would then lack the legacy routes.
        require $projectDir . '/routes.php';

        $this->addRouteDirectly('profile_all_comments', '/members/{username}/comments/');
        $this->addRouteDirectly('comment_add', '/members/{username}/comments/add');
        $this->addRouteDirectly('comment_edit', '/members/{username}/comments/edit');
        $this->addRouteDirectly('myvisitors', '/myvisitors');
        // $this->addRouteDirectly('profilecomments', '/about/commentguidelines');
        $this->addRouteDirectly('profile_addtorelations', '/members/{username}/relations/add');
        $this->addRouteDirectly('admin_editprofile_finish', '/members/{username}/adminedit/finish');
        // $this->addRouteDirectly('imprint', '/impressum');
        $this->addRouteDirectly('add_relation_finish', '/members/{username}/relations/add/finish');
        $this->addRouteDirectly(
            'delete_relation_editprofile',
            '/members/{username}/relations/delete/{relationId}/editprofile'
        );

        return $this->routes;
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        return 'legacy' === $type;
    }

    private function addRouteDirectly($name, $path): void
    {
        $path = preg_replace('^:(.*?):^', '{\1}', (string) $path);
        $this->routes->add($name, new Route($path, [
            '_controller' => 'rox.legacy_controller::showLegacyPage',
        ], [], [], '', [], ['get', 'post']));
    }

    private function addRoute($name, $path, $controller = '', $action = ''): void
    {
        $path = preg_replace('^:(.*?):^', '{\1}', (string) $path);
        $this->routes->add($name, new Route($path, [
            '_controller' => 'rox.legacy_controller::showLegacyPage',
        ], [], [], '', [], ['get', 'post']));
    }
}

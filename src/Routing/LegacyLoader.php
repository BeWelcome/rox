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

        $this->addRouteDirectly('editmyprofile', '/editmyprofile');
        $this->addRouteDirectly('editmyprofile_locale', '/editmyprofile/{locale}');
        $this->addRouteDirectly('gallery_show_user', '/gallery/show/user/{username}');
        $this->addRouteDirectly('gallery_show_user_images', '/gallery/show/user/{username}/pictures');
        $this->addRouteDirectly('gallery_show_user_images_pages', '/gallery/show/user/{username}/pictures/=page{pageNo}');
        $this->addRouteDirectly('gallery_show_user_albums', '/gallery/show/user/{username}/sets');
        $this->addRouteDirectly('gallery_show_user_albums_pages', '/gallery/show/user/{username}/sets/=page{pageNo}');
        $this->addRouteDirectly('gallery_show_user_latest', '/gallery/show/user/{username}/images');
        $this->addRouteDirectly('gallery_show_user_latest_pages', '/gallery/show/user/{username}/images/=page{pageNo}');
        $this->addRouteDirectly('gallery_show_image', '/gallery/show/image/{imageId}');
        $this->addRouteDirectly('gallery_album_show', '/gallery/show/sets/');
        $this->addRouteDirectly('gallery_album_new', '/gallery/show/sets/{galleryId}');
        $this->addRouteDirectly('gallery_album_delete', '/gallery/show/sets/{galleryId}/delete');
        $this->addRouteDirectly('gallery_album_delete_confirmation', '/gallery/show/sets/{galleryId}/delete/true');
        $this->addRouteDirectly('gallery_delete_image', '/gallery/show/image/{imageId}/delete');
        $this->addRouteDirectly('gallery_image', '/gallery/img');
        $this->addRouteDirectly('gallery_upload_image', '/gallery/upload');
        $this->addRouteDirectly('gallery_upload_finish', '/gallery/uploaded');
        $this->addRouteDirectly('gallery_manage', '/gallery/manage');
        $this->addRouteDirectly('gallery_manage_pages', '/gallery/manage/=page{pageNo}');
        $this->addRouteDirectly('gallery_thumbnail', '/gallery/thumbimg');
        $this->addRouteDirectly('gallery', '/gallery');
        $this->addRouteDirectly('profile_all_comments', '/members/{username}/comments/');
        $this->addRouteDirectly('comment_add', '/members/{username}/comments/add');
        $this->addRouteDirectly('comment_edit', '/members/{username}/comments/edit');
        $this->addRouteDirectly('mypreferences', '/mypreferences');
        $this->addRouteDirectly('myvisitors', '/myvisitors');
        // $this->addRouteDirectly('profilecomments', '/about/commentguidelines');
        $this->addRouteDirectly('profile_addtorelations', '/members/{username}/relations/add');
        $this->addRouteDirectly('editmyprofile_finish', '/editmyprofile/finish');
        $this->addRouteDirectly('editmyprofile_language_finish', '/editmyprofile/{language}/finish');
        $this->addRouteDirectly('editmyprofile_add_language_finish', '/editmyprofile/{language}/add');
        $this->addRouteDirectly('delete_profile_language', '/editmyprofile/{language}/delete');
        $this->addRouteDirectly('admin_editprofile_finish', '/members/{username}/adminedit/finish');
        $this->addRouteDirectly('myprofile_in_langauge', '/members/{username}/{language}');
        // $this->addRouteDirectly('imprint', '/impressum');
        $this->addRouteDirectly('add_relation_finish', '/members/{username}/relations/add/finish');
        $this->addRouteDirectly(
            'delete_relation_editprofile',
            '/members/{username}/relations/delete/{relationId}/editprofile'
        );
        $this->addRouteDirectly('messages_with', '/messages/with/{username}');
        $this->addRouteDirectly('check_notification', '/notify/{notificationId}/check');

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

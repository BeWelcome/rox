<?php

namespace AppBundle\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class LegacyLoader.
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
        require_once $dirfix.'routes.php';

        // Forum urls
        $this->addRouteDirectly('forums', '/forums');
        $this->addRouteDirectly('forums_new', '/forums/new');
        $this->addRouteDirectly('bwforum', 'forums/bwforum');
        $this->addRouteDirectly('forum_permalink', '/forums/s{threadId}/');
        $this->addRouteDirectly('forum_thread', '/forums/s{threadId}');
        $this->addRouteDirectly('forum_admin', '/forums/modfulleditpost/{threadId}');
        $this->addRouteDirectly('forum_tag', '/forums/t{tag}');
        $this->addRouteDirectly('forum_tag_detail_1', '/forums/t{tag}/{detail1}');
        $this->addRouteDirectly('forum_tag_detail_2', '/forums/t{tag}/{detail1}/{detail2}');
        $this->addRouteDirectly('forum_report', '/forums/reporttomod/{threadId}');
        $this->addRouteDirectly('forum_rules', '/forums/rules');
        $this->addRouteDirectly('forum_edit', '/forums/edit/m{postId}');
        $this->addRouteDirectly('forum_reply', '/forums/s{threadId}/reply');
        $this->addRouteDirectly('forum_translate', '/forums/translate/m{postId}');
        $this->addRouteDirectly('forum_reverse', '/forums/s{threadId}//reverse');
        $this->addRouteDirectly('subscriptions', '/forums/subscriptions');
        $this->addRouteDirectly('thread_subscribe', '/forums/subscriptions/subscribe/thread/{threadId}');
        $this->addRouteDirectly('thread_unsubscribe', '/forums/subscriptions/unsubscribe/thread/{threadId}/{subscriptionId}');
        $this->addRouteDirectly('thread_notifications_disable', '/forums/subscriptions/disable/thread/{threadId}/{subscriptionId}');
        $this->addRouteDirectly('thread_notifications_enable', '/forums/subscriptions/enable/thread/{threadId}/{subscriptionId}');
        $this->addRouteDirectly('group_notifications_enable', '/forums/subscriptions/enable/group/{groupId}');
        $this->addRouteDirectly('group_notifications_enable', '/forums/subscriptions/disable/group/{groupId}');
        $this->addRouteDirectly('group_subscribe', '/forums/subscriptions/subscribe/group/{groupId}');
        $this->addRouteDirectly('group_unsubscribe', '/forums/subscriptions/unsubscribe/group/{groupId}');
        $this->addRouteDirectly('community', '/community');
        $this->addRouteDirectly('faq', '/faq');
        $this->addRouteDirectly('about_faq', '/about/faq');
        $this->addRouteDirectly('faq_category', '/faq/{category}');
        $this->addRouteDirectly('about_faq_category', '/about/faq/{category}');
        $this->addRouteDirectly('about', '/about');
        $this->addRouteDirectly('stats', '/stats');
        $this->addRouteDirectly('stats_images', '/stats/{image}.png');
        $this->addRouteDirectly('getactive', '/about/getactive');
        $this->addRouteDirectly('contactus', '/about/feedback');
        $this->addRouteDirectly('signup', '/signup/');
        $this->addRouteDirectly('signup_1', '/signup/1');
        $this->addRouteDirectly('signup_2', '/signup/2');
        $this->addRouteDirectly('signup_3', '/signup/3');
        $this->addRouteDirectly('signup_4', '/signup/4');
        $this->addRouteDirectly('signup_finish', '/signup/finish');
        $this->addRouteDirectly('signup_email_check', '/signup/checkemail');
        $this->addRouteDirectly('signup_confirm', '/signup/confirm/{username}/{regkey}');
        return $this->routes;
    }

    public function supports($resource, $type = null)
    {
        return 'legacy' === $type;
    }

    private function addRoute($name, $path, $controller = '', $action = '')
    {
        $path = preg_replace('^:(.*?):^', '{\1}', $path);
        $this->routes->add($name, new Route($path, [
            '_controller' => 'rox.legacy_controller:showAction',
        ], [], [], '', [], ['get', 'post']));
    }

    private function addRouteDirectly($name, $path)
    {
        $path = preg_replace('^:(.*?):^', '{\1}', $path);
        $this->routes->add($name, new Route($path, [
            '_controller' => 'rox.legacy_controller:showAction',
        ], [], [], '', [], ['get', 'post']));
    }
}

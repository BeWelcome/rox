<?php

namespace App\Routing;

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

    /** @var boolean */
    private $loaded = false;

    /**
     * @param mixed $resource
     * @param null  $type
     *
     * @throws \RuntimeException
     *
     * @return RouteCollection
     */
    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Do not add the "legacy" loader twice');
        }

        $this->routes = new RouteCollection();

        // Handle current directory (difference between cache clear and web access)
        $projectDir = realpath(__DIR__.'/../..');

        // Include legacy routes to ensure firewall kicks in
        require_once $projectDir.'/routes.php';

        // Forum urls
//        $this->addRouteDirectly('forums', '/forums/page{pageGroups}/page{pageForums}');
        $this->addRouteDirectly('forums', '/forums');
        $this->addRouteDirectly('forums_pages', '/forums/page{groupsPage}/page{bwforumsPage}/');
        $this->addRouteDirectly('forums_new', '/forums/new');
        $this->addRouteDirectly('forums_search', '/forums/search/{keyword}');
        $this->addRouteDirectly('bwforum', 'forums/bwforum');
        $this->addRouteDirectly('forum_permalink', '/forums/s{threadId}/');
        $this->addRouteDirectly('forum_thread', '/forums/s{threadId}');
        $this->addRouteDirectly('forum_thread', '/forums/s{threadId}/page{pageId}');
        $this->addRouteDirectly('forum_admin', '/forums/modfulleditpost/{postId}');
        $this->addRouteDirectly('forum_admin_edit', '/forums/modeditpost/{postId}');
        $this->addRouteDirectly('forum_admin_edit_trailing', '/forums/modeditpost/{postId}/');
        $this->addRouteDirectly('forum_tag', '/forums/t{tag}');
        $this->addRouteDirectly('forum_tag_detail_1', '/forums/t{tag}/{detail1}');
        $this->addRouteDirectly('forum_tag_detail_2', '/forums/t{tag}/{detail1}/{detail2}');
        $this->addRouteDirectly('forum_report', '/forums/reporttomod/{postId}');
        $this->addRouteDirectly('forum_report_member', '/forums/reporttomod/{postId}/{memberId}');
        $this->addRouteDirectly('forum_rules', '/forums/rules');
        $this->addRouteDirectly('forum_edit', '/forums/edit/m{postId}');
        $this->addRouteDirectly('forum_reply', '/forums/s{threadId}/reply');
        $this->addRouteDirectly('forum_translate', '/forums/translate/m{postId}');
        $this->addRouteDirectly('forum_reverse', '/forums/s{threadId}//reverse');
        $this->addRouteDirectly('forum_posts_member', '/forums/member/{username}');
        $this->addRouteDirectly('forum_thread_subscription_enable', '/forums/subscriptions/enable/thread/{threadId}');
        $this->addRouteDirectly('forum_thread_subscription_disable', '/forums/subscriptions/disable/thread/{threadId}');
        $this->addRouteDirectly('forum_thread_subscription', '/forums/subscribe/thread/{threadId}');
        $this->addRouteDirectly('rss_feed', '/rss/forumthreads');
        $this->addRouteDirectly('subscriptions_disable', '/forums/subscriptions/disable');
        $this->addRouteDirectly('subscriptions_enable', '/forums/subscriptions/enable');
        $this->addRouteDirectly('subscriptions', '/forums/subscriptions');
        $this->addRouteDirectly('thread_subscribe', '/forums/subscriptions/subscribe/thread/{threadId}');
        $this->addRouteDirectly(
            'thread_unsubscribe',
            '/forums/subscriptions/unsubscribe/thread/{threadId}/{subscriptionId}'
        );
        $this->addRouteDirectly(
            'thread_notifications_disable',
            '/forums/subscriptions/disable/thread/{threadId}/{subscriptionId}'
        );
        $this->addRouteDirectly(
            'thread_notifications_enable',
            '/forums/subscriptions/enable/thread/{threadId}/{subscriptionId}'
        );
        $this->addRouteDirectly('group_notifications_enable', '/forums/subscriptions/enable/group/{groupId}');
        $this->addRouteDirectly('group_notifications_enable', '/forums/subscriptions/disable/group/{groupId}');
        $this->addRouteDirectly('group_subscribe', '/forums/subscriptions/subscribe/group/{groupId}');
        $this->addRouteDirectly('group_unsubscribe', '/forums/subscriptions/unsubscribe/group/{groupId}');
        $this->addRouteDirectly('group_new_topic', '/groups/{groupId}/forum/new');
        $this->addRouteDirectly('group_user_all', '/members/{username}/groups');
        $this->addRouteDirectly('group_add_related_group', '/groups/{groupId}/selectrelatedgroup');
        $this->addRouteDirectly('group_invite_member', '/groups/{groupId}/invitemembers/search');
        $this->addRouteDirectly('community', '/community');
        $this->addRouteDirectly('faq', '/faq');
        $this->addRouteDirectly('about_faq', '/about/faq');
        $this->addRouteDirectly('faq_category', '/faq/{category}');
        $this->addRouteDirectly('about_faq_category', '/about/faq/{category}');
        $this->addRouteDirectly('about', '/about');
        $this->addRouteDirectly('about_people', '/about/thepeople');
        $this->addRouteDirectly('stats', '/stats');
        $this->addRouteDirectly('stats_images', '/stats/{image}.png');
        $this->addRouteDirectly('getactive', '/about/getactive');
        $this->addRouteDirectly('contactus', '/about/feedback');
        $this->addRouteDirectly('feedback', '/feedback');
        $this->addRouteDirectly('feedback_submit', '/feedback/submit');
        $this->addRouteDirectly('privacy', '/privacy');
        $this->addRouteDirectly('signup', '/signup');
        $this->addRouteDirectly('signup_1', '/signup/1');
        $this->addRouteDirectly('signup_2', '/signup/2');
        $this->addRouteDirectly('signup_3', '/signup/3');
        $this->addRouteDirectly('signup_4', '/signup/4');
        $this->addRouteDirectly('signup_email_check', '/signup/checkemail');
        $this->addRouteDirectly('signup_handle_check', '/signup/checkhandle');
        $this->addRouteDirectly('deleteprofile', '/deleteprofile');
        $this->addRouteDirectly('editmyprofile', '/editmyprofile');
        $this->addRouteDirectly('editmyprofile_locale', '/editmyprofile/{locale}');
        $this->addRouteDirectly('donate', '/donate');
        $this->addRouteDirectly('donate_list', '/donate/list');
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
        $this->addRouteDirectly('profilecomments', '/about/commentguidelines');
        $this->addRouteDirectly('profile_addtorelations', '/members/{username}/relations/add');
        $this->addRouteDirectly('setlocation', '/setlocation');
        $this->addRouteDirectly('editmyprofile_finish', '/editmyprofile/finish');
        $this->addRouteDirectly('editmyprofile_language_finish', '/editmyprofile/{language}/finish');
        $this->addRouteDirectly('admin_editprofile_finish', '/members/{username}/adminedit/finish');
        $this->addRouteDirectly('myprofile_in_langauge', '/members/{username}/{language}');
        $this->addRouteDirectly('imprint', '/impressum');
        $this->addRouteDirectly('add_relation_finish', '/members/{username}/relations/add/finish');
        $this->addRouteDirectly(
            'delete_relation_editprofile',
            '/members/{username}/relations/delete/{relationId}/editprofile'
        );
        $this->addRouteDirectly('messages_with', '/messages/with/{username}');
        $this->addRouteDirectly('check_notification', '/notify/{notificationId}/check');

        // Simple newsletter page
        $this->addRouteDirectly('newsletters', '/newsletters');
        $this->addRouteDirectly('newsletter_single', '/newsletter/{shortCode}/{language}');

        // Polls
        $this->addRouteDirectly('polls', '/polls');
        $this->addRouteDirectly('polls_create', '/polls/create');
        $this->addRouteDirectly('polls_list_all', '/polls/listall');
        $this->addRouteDirectly('polls_listClose', '/polls/listClose');
        $this->addRouteDirectly('polls_listOpen', '/polls/listOpen');
        $this->addRouteDirectly('polls_listProject', '/polls/listProject');
        $this->addRouteDirectly('polls_cancelvote', '/polls/cancelvote/{pollId}');
        $this->addRouteDirectly('polls_contribute', '/polls/contribute/{pollId}');
        $this->addRouteDirectly('polls_vote', '/polls/vote');
        $this->addRouteDirectly('polls_update', '/polls/update/{pollId}');
        $this->addRouteDirectly('polls_doupdatepoll', '/polls/doupdatepoll');
        $this->addRouteDirectly('polls_addchoice', '/polls/addchoice');
        $this->addRouteDirectly('polls_updatechoice', '/polls/updatechoice');
        $this->addRouteDirectly('polls_createpoll', '/polls/createpoll');
        $this->addRouteDirectly('polls_view_results', '/polls/seeresults/{pollId}');

        return $this->routes;
    }

    public function supports($resource, $type = null)
    {
        return 'legacy' === $type;
    }

    private function addRouteDirectly($name, $path)
    {
        $path = preg_replace('^:(.*?):^', '{\1}', $path);
        $this->routes->add($name, new Route($path, [
            '_controller' => 'rox.legacy_controller::showAction',
        ], [], [], '', [], ['get', 'post']));
    }

    private function addRoute($name, $path, $controller = '', $action = '')
    {
        $path = preg_replace('^:(.*?):^', '{\1}', $path);
        $this->routes->add($name, new Route($path, [
            '_controller' => 'rox.legacy_controller::showAction',
        ], [], [], '', [], ['get', 'post']));
    }
}

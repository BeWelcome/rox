<?php

namespace Rox\Main\Home;

use Rox\Framework\Controller;
use Rox\Framework\TwigView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
* dashboard controller
*
* @package Dashboard
* @author Amnesiac84
*/
class HomeController extends Controller
{
    /**
     * @RoxModelBase Rox
     */
    private $_model;

    /**
     * @Router
     */
    public $_router;

// for some things we still need a class-scope view object
    private $_view;


    public function __construct()
    {
        $this->_model = new HomeModel();
    }

    public function __destruct()
    {
        unset($this->_model);
    }


    /**
     * @param int $all
     * @param int $unread
     * @return Response
     */
    public function showMessagesAction(Request $request) {
        $widget = new TwigView($this->getRouting(), false);
        $all = $request->query->get('all');
        $unread = $request->query->get('unread');
        $messages = $this->_model->getMessages($all, $unread, 4);
        $widget->setTemplate('messages.html.twig', 'messages', [
            'messages' => $messages,
        ]);
        return new Response($widget->render());
    }

    public function showNotificationsAction() {
        $widget = new TwigView($this->getRouting(), false);
        $notifications = $this->_model->getNotifications(5);
        $widget->setTemplate('notifications.html.twig', 'home', [
            'notifications' => $notifications,
        ]);
        return new Response($widget->render());
    }

    /**
     * @param int $groups
     * @param int $forum
     * @param int $following
     * @return Response
     */
    public function showThreadsAction(Request $request) {
        $widget = new TwigView($this->getRouting(), false);
        $groups = $request->query->get('groups');
        $forum = $request->query->get('forum');
        $following = $request->query->get('following');
        $threads = $this->_model->getThreads($groups, $forum, $following, 4);
        $widget->setTemplate('forums.html.twig', 'forums', [
            'threads' => $threads,
        ]);
        return new Response($widget->render());
    }

    public function showActivitiesAction() {
        $widget = new TwigView($this->getRouting(), false);
        $activities = $this->_model->getActivities(4);
        $widget->setTemplate('activities.html.twig', 'activities', [
            'activities' => $activities,
        ]);
        return new Response($widget->render());
    }

    /**
     * Shows the home page
     *
     * @return Response
     */
    public function showAction() {
        $page = new HomePage($this->getRouting());
        $memberDetails = $this->_model->getMemberDetails();
        $page->addParameters($memberDetails);
        return new Response($page->render());
    }
}
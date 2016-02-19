<?php

namespace Rox\Main\Home;

use Rox\Framework\Controller;
use Rox\Framework\TwigView;
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

    public function showMessagesAction() {
        $widget = new TwigView($this->getRouting(), false);
        $messages = $this->_model->getMessages(4);
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

    public function showThreadsAction($groups, $forum, $following) {
        $widget = new TwigView($this->getRouting(), false);
        $threads = $this->_model->getThreads($groups, $forum, $following, 4);
        $widget->setTemplate('forums.html.twig', 'forums', [
            'threads' => $threads,
        ]);
        return new Response($widget->render());
    }

    public function showActivitiesAction() {
        $widget = new TwigView($this->getRouting(), false);
        $messages = $this->_model->getMessages(4);
        $widget->setTemplate('messages.html.twig', 'messages', [
            'messages' => $messages,
        ]);
        return new Response($widget->render());
    }

    /**
     *

     *
    {% set notifications = [
    {
    'title': "This is a system notification",
    'user': 'Member-110',
    'time': '10 minutes ago',
    'read': true
    },
    {
    'title': "Another system notification",
    'user': 'Member-111',
    'time': '1 hour ago',
    'read': true
    },
    {
    'title': "And another one",
    'user': 'Member-112',
    'time': '10 days ago',
    'read': true
    },
    {
    'title': "The fourth one",
    'user': 'Member-113',
    'time': '2 years ago',
    'read': true
    },
    {
    'title': "And the last to be shown",
    'user': 'Member-114',
    'time': '7 years ago',
    'read': true
    }
    ]
    %}

    {% set posts = [
    {
    'title': "This could well be the title of the forum thread",
    'href': 'groups/353/forum/s12345-this-is-just-a-test-link',
    'lastuser': 'Member-120',
    'time': '10 minutes ago',
    'read': true ,
    'forum': true ,
    'group': false ,
    'following': false ,
    },
    {
    'title': "The donation campaign has started!",
    'href': 'forums/s15476-Donation_campaign_for_Bewelcome_has_started_',
    'lastuser': 'Member-121',
    'time': '14 minutes ago',
    'read': true ,
    'forum': true ,
    'group': false ,
    'following': false ,
    },
    {
    'title': "The third ever to be published fake thread",
    'href': 'groups/123/forum/s12345-whatever',
    'lastuser': 'Member-122',
    'time': '20 minutes ago',
    'read': true ,
    'forum': false ,
    'group': true ,
    'following': false ,
    },
    {
    'title': "What happened if I'd make this a real, real long title, so that it would most definitely not fit",
    'href': 'groups/353/forum/s12345-this-is-just-a-test-link',
    'lastuser': 'Member-123',
    'time': '45 minutes ago',
    'read': false ,
    'forum': true ,
    'group': false ,
    'following': true ,
    }
    ]
    %}

    {% set activities = [
    {
    'title': "This is an activity near you!",
    'id': 1,
    'place': 'Berlin',
    'country': 'Germany',
    'year': 2016,
    'month': 02,
    'day': 01,
    'yes': 2,
    'maybe': 25,
    'read': true
    },
    {
    'title': "Eating peanuts with people I don't know",
    'id': 2,
    'place': 'Amsterdam',
    'country': 'Netherlands',
    'year': 2016,
    'month': 03,
    'day': 15,
    'yes': 20,
    'maybe': 400,
    'read': false
    },
    {
    'title': "What happens if the activity title is way longer than the place reserved for it?",
    'id': 3,
    'place': 'Paris',
    'country': 'France',
    'year': 2017,
    'month': 10,
    'day': 30,
    'yes': 0,
    'maybe': 1,
    'read': true
    },

    ]
    %}

     * @return Response
     */
    public function showAction() {
        $page = new HomePage($this->getRouting());
        return new Response($page->render());
    }
}
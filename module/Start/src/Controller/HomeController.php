<?php

namespace Rox\Start\Controller;

use Rox\Main\Home\HomeModel;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * dashboard controller
 *
 * @package Dashboard
 * @author Amnesiac84
 */
class HomeController
{
    /**
     * @var EngineInterface
     */
    protected $engine;

    /**
     * @var SessionInterface
     */
    protected $session;

    public function __construct(EngineInterface $engine, SessionInterface $session)
    {
        $this->engine = $engine;
        $this->session = $session;
    }

    /**
     * @todo
     */
    public function avatar()
    {
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function showMessagesAction(Request $request)
    {
        $model = new HomeModel();

        $all = $request->query->get('all');
        $unread = $request->query->get('unread');

        $messages = $model->getMessages($all, $unread, 4);

        $content = $this->engine->render('@start/widget/messages.html.twig', [
            'messages' => $messages,
        ]);

        return new Response($content);
    }

    public function showNotificationsAction()
    {
        $model = new HomeModel();

        $notifications = $model->getNotifications(5);

        $content = $this->engine->render('@start/widget/notifications.html.twig', [
            'notifications' => $notifications,
        ]);

        return new Response($content);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function showThreadsAction(Request $request)
    {
        $groups = $request->query->get('groups');
        $forum = $request->query->get('forum');
        $following = $request->query->get('following');

        $model = new HomeModel();

        $threads = $model->getThreads($groups, $forum, $following, 4);

        $content = $this->engine->render('@start/widget/forums.html.twig', [
            'threads' => $threads,
        ]);

        return new Response($content);
    }

    public function showActivitiesAction()
    {
        $model = new HomeModel();

        $activities = $model->getActivities(4);

        $content = $this->engine->render('@start/widget/activities.html.twig', [
            'activities' => $activities,
        ]);

        return new Response($content);
    }

    /**
     * Shows the home page
     *
     * @return Response
     */
    public function showAction()
    {
        if (!$this->session->get('IdMember')) {
            return new RedirectResponse('/');
        }

        $content = $this->engine->render('@start/home.html.twig');

        return new Response($content);
    }
}

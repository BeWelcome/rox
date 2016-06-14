<?php

namespace Rox\Start\Controller;

use Rox\Main\Home\HomeModel as HomeService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
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
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var HomeService
     */
    protected $homeService;

    public function __construct(
        EngineInterface $engine,
        TokenStorageInterface $tokenStorage
    ) {
        $this->engine = $engine;
        $this->tokenStorage = $tokenStorage;

        $this->homeService = new HomeService();
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function showMessagesAction(Request $request)
    {
        $all = $request->query->get('all');
        $unread = $request->query->get('unread');

        $member = $this->tokenStorage->getToken()->getUser();

        $messages = $this->homeService->getMessages($member, $all, $unread, 4);

        $content = $this->engine->render('@start/widget/messages.html.twig', [
            'messages' => $messages,
        ]);

        return new Response($content);
    }

    public function showNotificationsAction()
    {
        $notifications = $this->homeService->getNotifications(5);

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

        $threads = $this->homeService->getThreads($groups, $forum, $following, 4);

        $content = $this->engine->render('@start/widget/forums.html.twig', [
            'threads' => $threads,
        ]);

        return new Response($content);
    }

    public function showActivitiesAction()
    {
        $activities = $this->homeService->getActivities(4);

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
        $content = $this->engine->render('@start/home.html.twig');

        return new Response($content);
    }
}

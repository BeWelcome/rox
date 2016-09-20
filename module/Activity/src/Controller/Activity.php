<?php

namespace Rox\Activity\Controller;

use Rox\CommunityNews\Model\CommunityNews;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ActivityController extends Controller
{
    public function listAction(Request $request)
    {
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 15);

        $activityRepository = new Activity();

        list($activity, $count) = $activityRepository->getAll($page, $limit);

        $content = $this->render('@activity/activity/list.html.twig', [
            'activity' => $activity,
            'filter' => $request->query->all(),
            'page' => $page,
            'pages' => ceil($count/$limit),
        ]);

        return new Response($content);
    }

    public function showAction($id)
    {
        $activityRepository = new CommunityNews();
        $activity = $activityRepository->getById($id);

        $content = $this->render('@activity/activity/show.html.twig', [
            'activity' => $communityNews,
        ]);

        return new Response($content);
    }
}

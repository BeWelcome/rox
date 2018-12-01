<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Model\ActivityModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ActivityController extends AbstractController
{
    /**
     * @Route("/activity", name="activity")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Request $request)
    {
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 15);

        $activityModel = new ActivityModel($this->getDoctrine());
        $activities = $activityModel->getLatest($page, $limit);

        return $this->render('activity/list.html.twig', [
            'active' => 'ActivitiesNearMe',
            'activities' => $activities,
        ]);
    }

    /**
     * @Route("/activity/{id}", name="activity_show",
     *     requirements={"id": "\d+"})
     *
     * @param Activity $activity
     *
     * @return Response
     */
    public function showAction(Activity $activity)
    {
        $content = $this->render('activity/show.html.twig', [
            'activity' => $activity,
        ]);

        return new Response($content);
    }

    /**
     * @Route("/activity/{id}/edit", name="activity_edit",
     *     requirements={"id": "\d+"})
     *
     * @param Activity $activity
     *
     * @return Response
     */
    public function editAction(Activity $activity)
    {
        $content = $this->render('activity/show.html.twig', [
            'activity' => $activity,
        ]);

        return new Response($content);
    }
}

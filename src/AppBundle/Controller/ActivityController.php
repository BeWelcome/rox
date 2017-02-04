<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Activity;
use AppBundle\Model\ActivityModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ActivityController extends Controller
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

        $activityModel = new ActivityModel( $this->getDoctrine() );
        $activities = $activityModel->getLatest($page, $limit);

        return $this->render(':activity:list.html.twig', [
            'active' => 'ActivitiesNearMe',
            'activities' => $activities
        ]);
    }

    /**
     * @Route("/activity/{id}", name="activity_show",
     *     requirements={"id": "\d+"})
     *
     * @param Activity $activity
     * @return Response
     */
    public function showAction(Activity $activity)
    {
        $content = $this->render(':activity:show.html.twig', [
            'activity' => $activity,
        ]);

        return new Response($content);
    }

    /**
     * @Route("/activity/{id}/edit", name="activity_edit",
     *     requirements={"id": "\d+"})
     *
     * @param Activity $activity
     * @return Response
     */
    public function editAction(Activity $activity)
    {
        $content = $this->render(':activity:show.html.twig', [
            'activity' => $activity,
        ]);

        return new Response($content);
    }
}

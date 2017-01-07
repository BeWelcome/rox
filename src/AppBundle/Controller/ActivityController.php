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

        $content = $this->render(':activity:list.html.twig', [
            'active' => 'ActivitiesNearMe',
            'subitems' => [
                'ActivitiesNearMe' => [ 'route' => 'activity' ],
                'ActivitiesPast' => [ 'route' => 'activity' ],
                'ActivitiesUpcoming' => [ 'route' => 'activity' ],
                'ActivitiesCreate' => [ 'route' => 'activity' ],
            ],
            'activities' => $activities
        ]);

        return new Response($content);
    }

    /**
     * @Route("/activity/{id}", name="activity_show",
     *     requirements={"id": "\d+"})
     *
     * @return \Symfony\Component\HttpFoundation\Response
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Activity $activity)
    {
        $content = $this->render(':activity:show.html.twig', [
            'activity' => $activity,
        ]);

        return new Response($content);
    }
}

<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Activity;
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
//         $limit = $request->query->get('limit', 15);

        $activities = $this->getDoctrine()->getRepository(Activity::class)->findLatest($page);

        $content = $this->render(':activity:list.html.twig', [
            'activities' => $activities
        ]);

        return new Response($content);
    }

    public function showAction(Activity $activity)
    {
        $content = $this->render(':activity:show.html.twig', [
            'activity' => $activity,
        ]);

        return new Response($content);
    }
}

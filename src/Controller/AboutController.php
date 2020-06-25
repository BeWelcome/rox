<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AboutController extends AbstractController
{
    /**
     * @Route("/about/credits", name="credits")
     *
     * @return Response
     */
    public function showCredits()
    {
        return $this->render('about/credits.html.twig', [
            'submenu' => [
                'items' => $this->getSubMenuItems(),
                'active' => 'credits',
            ],
        ]);
    }

    /**
     * @Route("/about/statistics", name="statistics")
     *
     * @return Response
     */
    public function showStatistics()
    {
        $statistics = [
            'members' => [
                'headline' => 'members',
                'route' => 'stats_members',
            ],
            'sent_messages' => [
                'headline' => 'sent_messages',
                'route' => 'stats_messages_sent',
            ],
            'read_messages' => [
                'headline' => 'read_messages',
                'route' => 'stats_messages_read',
            ],
            'sent_requests' => [
                'headline' => 'sent_requests',
                'route' => 'stats_requests_sent',
            ],
            'accepted_requests' => [
                'headline' => 'accepted_requests',
                'route' => 'stats_requests_accepted',
            ],
        ];
        return $this->render('about/statistics.html.twig', [
            'statistics' => $statistics,
            'submenu' => [
                'items' => $this->getSubMenuItems(),
                'active' => 'statistics',
            ],
        ]);
    }

    /**
     * @return array
     */
    private function getSubMenuItems()
    {
        return [
            'credits' => [
                'key' => 'credits.title',
                'url' => $this->generateUrl('credits'),
            ],
            'statistics' => [
                'key' => 'statistics.title',
                'url' => $this->generateUrl('statistics'),
            ],
        ];
    }
}

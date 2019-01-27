<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NotificationController extends AbstractController
{
    /**
     * @Route("/notifications", name="notifications")
     *
     * @return Response
     */
    public function showNotificationsActions()
    {
        return new Response('Hallo');
    }
}

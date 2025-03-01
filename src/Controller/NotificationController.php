<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NotificationController extends AbstractController
{
    /**
     * @return Response
     */
    #[Route(path: '/notifications', name: 'notifications')]
    public function showNotificationsActions()
    {
        return new Response('Hallo');
    }
}

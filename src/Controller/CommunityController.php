<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CommunityController extends AbstractController
{
    /**
     * @return Response
     */
    #[Route(path: '/community', name: 'community')]
    public function showCommunity()
    {
        return $this->render('community/community.html.twig');
    }
}

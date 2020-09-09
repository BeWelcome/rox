<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommunityController extends AbstractController
{
    /**
     * @Route("/community", name="community")
     *
     * @return Response
     */
    public function showCommunity()
    {
        return $this->render('community/community.html.twig');
    }
}

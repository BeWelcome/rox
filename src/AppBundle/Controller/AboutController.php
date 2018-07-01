<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AboutController extends Controller
{
    /**
     * @Route("/about/credits", name="credits")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showCreditsAction()
    {
        return $this->render(':about:credits.html.twig');
    }
}
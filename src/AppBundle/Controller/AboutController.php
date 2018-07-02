<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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

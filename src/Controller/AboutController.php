<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class AboutController extends Controller
{
    /**
     * @Route("/about/credits", name="credits")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showCreditsAction()
    {
        return $this->render('about/credits.html.twig');
    }
}

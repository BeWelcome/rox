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
    public function showCreditsAction()
    {
        return $this->render('about/credits.html.twig', [
            'submenu' => [
                'items' => $this->getSubMenuItems(),
                'active' => 'credits',
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
        ];
    }
}

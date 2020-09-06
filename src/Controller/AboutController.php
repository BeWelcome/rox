<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AboutController extends AboutBaseController
{
    /**
     * @Route("/about", name="about")
     *
     * @return Response
     */
    public function showAbout()
    {
        return $this->render('about/about.html.twig', [
            'submenu' => [
                'items' => $this->getSubMenuItems(),
                'active' => 'about',
            ],
        ]);
    }

    /**
     * @Route("/about/thepeople", name="about_people")
     *
     * @return Response
     */
    public function showAboutThePeople()
    {
        return $this->render('about/thepeople.html.twig', [
            'submenu' => [
                'items' => $this->getSubMenuItems(),
                'active' => 'about_people',
            ],
        ]);
    }

    /**
     * @Route("/about/getactive", name="getactive")
     * @Route("/volunteer", name="volunteer")
     *
     * @return Response
     */
    public function showAboutGetActive()
    {
        return $this->render('about/getactive.html.twig', [
            'submenu' => [
                'items' => $this->getSubMenuItems(),
                'active' => 'getactive',
            ],
        ]);
    }

    /**
     * @Route("/about", name="about_theidea")
     *
     * @return Response
     */
    public function showAboutTheIdea()
    {
        return $this->render('about/about.html.twig', [
            'submenu' => [
                'items' => $this->getSubMenuItems(),
                'active' => 'about_theidea',
            ],
        ]);
    }

    /**
     * @Route("/press-information", name="about_press")
     * @Route("/media", name="media")
     *
     * @return Response
     */
    public function showAboutPressInfo()
    {
        return $this->render('about/pressinfo.html.twig', [
            'submenu' => [
                'items' => $this->getSubMenuItems(),
                'active' => 'about_press',
            ],
        ]);
    }

    /**
     * @Route("/bod", name="about_bod")
     *
     * @return RedirectResponse
     */
    public function showAboutBod()
    {
        return $this->redirect('https://www.bevolunteer.org/about-bevolunteer/board-of-directors/');
    }

    /**
     * @Route("bv", name="about_bv")
     *
     * @return RedirectResponse
     */
    public function showAboutBv()
    {
        return $this->redirect('https://www.bevolunteer.org/');
    }

    /**
     * @Route("/about/commentguidelines", name="profilecomments")
     *
     * @return Response
     */
    public function showAboutCommentGuidelines()
    {
        return $this->render('about/commentsguidelines.html.twig', [
            'submenu' => [
                'items' => $this->getSubMenuItems(),
                'active' => 'about_commentguidelines',
            ],
        ]);
    }

    /**
     * @Route("/about/credits", name="about_credits")
     *
     * @return Response
     */
    public function showAboutCredits()
    {
        return $this->render('about/credits.html.twig', [
            'submenu' => [
                'items' => $this->getSubMenuItems(),
                'active' => 'about_credits',
            ],
        ]);
    }

    /**
     * @Route("/impressum", name="imprint")
     *
     * @return Response
     */
    public function showImpressum()
    {
        return $this->render('about/impressum.html.twig', [
            'submenu' => [
                'items' => $this->getSubMenuItems(),
                'active' => 'about',
            ],
        ]);
    }
}

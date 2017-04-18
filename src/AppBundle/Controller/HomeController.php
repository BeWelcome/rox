<?php

namespace AppBundle\Controller;

use AppBundle\Form\LoginFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HomeController extends Controller
{
    /**
     * @Route("/", name="homepage")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $member = $this->getUser();
        if ($member) {
            return $this->redirectToRoute('landingpage');
        }

        $form = $this->createForm(LoginFormType::class, null, [
            'action' => $this->generateUrl('security_check'),
            'method' => 'POST',
        ]);

        return $this->render(':home:home.html.twig', [
            'form' => $form->createView(),
            'locale' => $this->getParameter('locale'),
            'title' => 'BeWelcome',
            'stats' => [
                'members' => 1000,
                'countries' => 72,
                'languages' => 17,
                'comments' => 1234,
                'activities' => 10,
            ],
        ]);
    }
}

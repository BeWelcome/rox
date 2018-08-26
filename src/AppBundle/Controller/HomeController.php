<?php

namespace AppBundle\Controller;

use AppBundle\Form\LoginFormType;
use AppBundle\Model\StatisticsModel;
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
            return $this->forward(LandingController::class . "::indexAction");
        }

        $form = $this->createForm(LoginFormType::class, null, [
            'action' => $this->generateUrl('security_check'),
            'method' => 'POST',
        ]);

        $statisticsModel = new StatisticsModel($this->getDoctrine());
        $statistics = $statisticsModel->getStatistics();

        return $this->render(':home:home.html.twig', [
            'form' => $form->createView(),
            'locale' => $this->getParameter('locale'),
            'title' => 'BeWelcome',
            'stats' => $statistics,
        ]);
    }
}

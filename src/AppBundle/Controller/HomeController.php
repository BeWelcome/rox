<?php

namespace AppBundle\Controller;

use AppBundle\Form\CustomDataClass\SearchFormRequest;
use AppBundle\Form\LoginFormType;
use AppBundle\Form\SearchFormType;
use AppBundle\Model\StatisticsModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotBlank;

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
            return $this->forward(LandingController::class.'::indexAction');
        }

        $loginForm = $this->createForm(LoginFormType::class, null, [
            'action' => $this->generateUrl('security_check'),
            'method' => 'POST',
        ]);

        // Find all members around 100km of the given location
        $searchFormRequest = new SearchFormRequest();
        $searchFormRequest->accommodation_neverask = true;
        $searchFormRequest->inactive = true;
        $searchFormRequest->distance = 100;

        $searchForm = $this->createForm(SearchFormType::class, $searchFormRequest, [
            'action' => '/search/map',
        ]);

        $usernameForm = $this->createFormBuilder()
            ->add('username', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->setAction('/signup/1')
            ->setMethod('POST')
            ->getForm();

        $statisticsModel = new StatisticsModel($this->getDoctrine());
        $statistics = $statisticsModel->getStatistics();
        $roxPostHandler = new \RoxPostHandler();
        $roxPostHandler->classes = [
            'SignupController',
        ];

        return $this->render(':home:home.html.twig', [
            'postHandler' => $roxPostHandler,
            'form' => $loginForm->createView(),
            'search' => $searchForm->createView(),
            'username' => $usernameForm->createView(),
            'locale' => $this->getParameter('locale'),
            'title' => 'BeWelcome',
            'stats' => $statistics,
        ]);
    }
}

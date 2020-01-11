<?php

namespace App\Controller;

use App\Form\CustomDataClass\SearchFormRequest;
use App\Form\LoginFormType;
use App\Form\SearchFormType;
use App\Model\StatisticsModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\NotBlank;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     *
     * @param StatisticsModel $statisticsModel
     *
     * @return Response
     */
    public function indexAction(StatisticsModel $statisticsModel)
    {
        $member = $this->getUser();
        if ($member) {
            return $this->forward(LandingController::class . '::indexAction');
        }

        $loginForm = $this->createForm(LoginFormType::class, null, [
            'action' => $this->generateUrl('security_check'),
            'method' => 'POST',
        ]);

        // Find all members around 100km of the given location
        $searchFormRequest = new SearchFormRequest($this->getDoctrine()->getManager());
        $searchFormRequest->showmap = true;
        $searchFormRequest->accommodation_neverask = true;
        $searchFormRequest->inactive = true;
        $searchFormRequest->distance = 100;

        $formFactory = $this->get('form.factory');
        $searchForm = $formFactory->createNamed('map', SearchFormType::class, $searchFormRequest, [
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

        $statistics = $statisticsModel->getStatistics();
        $roxPostHandler = new \RoxPostHandler();
        $roxPostHandler->setClasses([
            'SignupController',
        ]);

        return $this->render('home/home.html.twig', [
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

<?php

namespace App\Controller;

use App\Form\LoginFormType;
use App\Form\MapSearchFormType;
use App\Model\StatisticsModel;
use RoxPostHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\NotBlank;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     *
     * @return Response
     */
    public function show(Request $request, StatisticsModel $statisticsModel, array $locales)
    {
        $member = $this->getUser();
        if ($member) {
            return $this->forward(LandingController::class . '::show');
        }

        $loginForm = $this->createForm(LoginFormType::class, null, [
            'action' => $this->generateUrl('security_check'),
            'method' => 'POST',
        ]);

        // Find all members around 100km of the given location
        $searchForm = $this->createForm(MapSearchFormType::class, null, [
            'action' => '/search/map',
        ]);

        $usernameForm = $this->createFormBuilder()
            ->add('username', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->setAction('/signup')
            ->setMethod('POST')
            ->getForm();

        $statistics = $statisticsModel->getStatisticsHomepage();
        $roxPostHandler = new RoxPostHandler();
        $roxPostHandler->setClasses([
            'SignupController',
        ]);

        $images = $this->getHomeImagesRandomly();

        return $this->render('home/home.html.twig', [
            'postHandler' => $roxPostHandler,
            'form' => $loginForm->createView(),
            'search' => $searchForm->createView(),
            'images' => $images,
            'username' => $usernameForm->createView(),
            'locale' => $request->getPreferredLanguage($locales),
            'title' => 'BeWelcome',
            'stats' => $statistics,
        ]);
    }

    private function getHomeImagesRandomly()
    {
        $images = [
            1 => [
                'high' => '/images/homepicture-1200px_1-min.jpg',
                'low' => '/images/homepicture-576px_1-min.jpg',
            ],
            2 => [
                'high' => '/images/homepicture-1200px_2-min.jpg',
                'low' => '/images/homepicture-576px_2-min.jpg',
            ],
            3 => [
                'high' => '/images/homepicture-1200px_3-min.jpg',
                'low' => '/images/homepicture-576px_3-min.jpg',
            ],
        ];
        $picked = [];
        $imagesRandom = [];
        while (count($picked) <> 3) {
            $pick = random_int(1,3);
            if (!in_array($pick, $picked)) {
                $picked[] = $pick;
                $imagesRandom[count($picked)] = $images[$pick];
            }
        }

        return $imagesRandom;
    }
}

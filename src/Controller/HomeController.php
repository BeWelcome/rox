<?php

namespace App\Controller;

use App\Entity\Location;
use App\Form\MapSearchFormType;
use App\Model\StatisticsModel;
use Doctrine\ORM\EntityManagerInterface;
use RoxPostHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\NotBlank;

class HomeController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route(path: '/', name: 'homepage')]
    public function show(Request $request, StatisticsModel $statisticsModel, EntityManagerInterface $entityManager, array $locales): Response
    {
        $locationRepository = $entityManager->getRepository(Location::class);
        $city = $locationRepository->find(2797114);

        $member = $this->getUser();
        if ($member) {
            return $this->forward(LandingController::class . '::show');
        }

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

        $images = $this->getHomeImages();

        return $this->render('home/home.html.twig', [
            'postHandler' => $roxPostHandler,
            'search' => $searchForm->createView(),
            'images' => $images,
            'username' => $usernameForm->createView(),
            'locale' => $request->getPreferredLanguage($locales),
            'title' => 'BeWelcome',
            'stats' => $statistics,
        ]);
    }

    private function getHomeImages(): array
    {
        return [
            1 => [
                'high' => '/images/homepicture-big-1.jpg',
                'low' => '/images/homepicture-small-1.jpg',
            ],
            2 => [
                'high' => '/images/homepicture-big-2.jpg',
                'low' => '/images/homepicture-small-2.jpg',
            ],
            3 => [
                'high' => '/images/homepicture-big-3.jpg',
                'low' => '/images/homepicture-small-3.jpg',
            ],
        ];
    }
}

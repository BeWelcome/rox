<?php

namespace App\Model\MockupProvider;

use App\Doctrine\SubtripOptionsType;
use App\Doctrine\TripAdditionalInfoType;
use App\Entity\ForumPost;
use App\Entity\ForumThread;
use App\Entity\HostingRequest;
use App\Entity\Location;
use App\Entity\Member;
use App\Entity\Message;
use App\Entity\Subject;
use App\Entity\Subtrip;
use App\Entity\Trip;
use App\Form\CustomDataClass\SearchFormRequest;
use App\Form\DataTransformer\DateTimeTransformer;
use App\Form\InvitationGuest;
use App\Form\InvitationHost;
use App\Form\InvitationType;
use App\Form\ResetPasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Form\SearchFormType;
use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class HomeAndLoginMockups implements MockupProviderInterface
{
    private const MOCKUPS = [
        'homepage' => [
            'type' => 'page',
            'url' => '/',
            'template' => 'home/home.html.twig',
            'description' => 'The page that is shown to unauthenticated visitors.',
        ],
        'login' => [
            'type' => 'page',
            'url' => '/login',
            'template' => 'security/login.html.twig',
            'description' => 'The login page (without error message)',
        ],
    ];

    private FormFactoryInterface $formFactory;
    private EntityManagerInterface $entityManager;

    public function __construct(FormFactoryInterface $formFactory, EntityManagerInterface $entityManager)
    {
        $this->formFactory = $formFactory;
        $this->entityManager = $entityManager;
    }

    public function getFeature(): string
    {
        return 'home_and_login';
    }

    public function getMockups(): array
    {
        return self::MOCKUPS;
    }

    public function getMockupVariables(array $parameters): array
    {
        switch ($parameters['name']) {
            case 'homepage':
                $searchFormRequest = new SearchFormRequest($this->entityManager);
                $searchFormRequest->show_map = true;
                $searchFormRequest->accommodation_neverask = true;
                $searchFormRequest->inactive = true;
                $searchFormRequest->distance = 100;
                $searchForm = $this->formFactory->createNamed('map', SearchFormType::class, $searchFormRequest, [
                    'action' => '/search/map',
                ]);

                $formBuilder = $this->formFactory->createBuilder();
                $usernameForm = $formBuilder
                    ->add('username', TextType::class, [
                        'constraints' => [
                            new NotBlank(),
                        ],
                    ])
                    ->getForm()
                ;
                return [
                    'stats' => [
                        'members' => 100000,
                        'languages' => 210,
                        'countries' => 192,
                        'comments' => 50000,
                        'activities' => 1300,
                    ],
                    'username' => $usernameForm->createView(),
                    'search' => $searchForm->createView(),
                ];
            case 'login':
                return [
                    'error' => null,
                    'last_username' => $parameters['user']->getUsername(),
                    'invalid_credentials' => false,
                    'resend_confirmation' => false,
                    'member_banned' => false,
                    'member_expired' => false,
                    'member_not_allowed_to_login' => false,
                ];
            default:
                return [];
        }
    }

    public function getMockupParameter(?string $locale = null, ?string $feature = null): array
    {
        return [];
    }
}

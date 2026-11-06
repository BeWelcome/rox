<?php

namespace App\Model\MockupProvider;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class HomeAndLoginMockups implements MockupProviderInterface
{
    private const array MOCKUPS = [
        'login reminder (email)' => [
            'type' => 'email',
            'template' => 'emails/login.reminder.html.twig',
            'description' => 'To members who haven\'t logged in for a year.',
        ],
        'suspension notice (email)' => [
            'type' => 'email',
            'template' => 'emails/suspension.notice.html.twig',
            'description' => 'To members who didn\'t come back despite the reminders: when their profile will be deleted.',
        ],
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

    public function __construct(private readonly FormFactoryInterface $formFactory, private readonly EntityManagerInterface $entityManager)
    {
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
                    'images' => [
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
                    ],
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
            case 'login reminder (email)':
                return ['receiver' => $parameters['user']];
            case 'suspension notice (email)':
                return ['receiver' => $parameters['user'], 'deletion_date' => strtotime('+2 months')];
            default:
                return [];
        }
    }

    public function getMockupParameter(?string $locale = null, ?string $feature = null): array
    {
        return [];
    }
}

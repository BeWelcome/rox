<?php

namespace App\Model\MockupProvider;

use App\Doctrine\SubtripOptionsType;
use App\Doctrine\TripAdditionalInfoType;
use App\Entity\HostingRequest;
use App\Entity\Location;
use App\Entity\Member;
use App\Entity\Message;
use App\Entity\Newsletter;
use App\Entity\Subject;
use App\Entity\Subtrip;
use App\Entity\Trip;
use App\Form\DataTransformer\DateTimeTransformer;
use App\Form\InvitationGuest;
use App\Form\InvitationHost;
use App\Form\InvitationType;
use App\Form\NewsletterUnsubscribeType;
use App\Form\ResetPasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Symfony\Component\Form\FormFactoryInterface;

class NewslettersMockups implements MockupProviderInterface
{
    private const MOCKUPS = [
        'Newsletters' => [
            'type' => 'email',
            'with_parameters' => true,
            'template' => 'emails/newsletter.html.twig',
            'description' => 'Email send to users who signuped for local event notifications',
            'setup' => 'getNewsletterParameters',
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
        return 'newsletters';
    }

    public function getMockups(): array
    {
        return self::MOCKUPS;
    }

    public function getMockupVariables(array $parameters): array
    {
        $newsletterRepository = $this->entityManager->getRepository(Newsletter::class);
        $newsletters = $newsletterRepository->findBy(['type' => $parameters['type']], ['created' => 'DESC']);

        if (0 === \count($newsletters)) {
            throw new \Exception('Sorry, no newsletter of type ' . $parameters['type'] . ' found, please create one.');
        }

        return [
            'wordcode' => strtolower('broadcast_body_' . $newsletters[0]->getName()),
            'unsubscribe_key' => '91aeecc7154b8fc9b2855a331e975bc8aafb088b6617d9aefe543e5fee427ae7',
            'newsletter' => $newsletters[0],
            'receiver' => $parameters['user'],
        ];
    }

    public function getMockupParameter(?string $locale = null, ?string $feature = null): array
    {
        return [
            'type' => [
                'regular' => Newsletter::REGULAR_NEWSLETTER,
                'local' => Newsletter::SPECIFIC_NEWSLETTER,
                'terms' => Newsletter::TERMS_OF_USE,
            ]
        ];
    }
}

<?php

namespace App\Model\MockupProvider;

use App\Entity\Newsletter;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Form\FormFactoryInterface;

class NewslettersMockups implements MockupProviderInterface
{
    private const array MOCKUPS = [
        'Newsletters' => [
            'type' => 'email',
            'with_parameters' => true,
            'template' => 'emails/newsletter.html.twig',
            'description' => 'Email send to users who signuped for local event notifications',
            'setup' => 'getNewsletterParameters',
        ],
    ];

    public function __construct(private readonly FormFactoryInterface $formFactory, private readonly EntityManagerInterface $entityManager)
    {
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
        $newsletter = $newsletterRepository->findOneBy(['type' => $parameters['type']], ['created' => 'DESC']);
        if (null === $newsletter) {
            // none of this type yet: the newest of any type shows how one would look (a copy, never saved)
            $newest = $newsletterRepository->findOneBy([], ['created' => 'DESC']);
            if (null === $newest) {
                throw new Exception('Sorry, there is no newsletter yet, please create one.');
            }
            $newsletter = clone $newest;
            $newsletter->setType($parameters['type']);
        }

        return [
            'wordcode' => strtolower('broadcast_body_' . $newsletter->getName()),
            'unsubscribe_key' => '91aeecc7154b8fc9b2855a331e975bc8aafb088b6617d9aefe543e5fee427ae7',
            'newsletter' => $newsletter,
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
            ],
        ];
    }
}

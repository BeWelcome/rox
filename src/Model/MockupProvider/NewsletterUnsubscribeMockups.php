<?php

namespace App\Model\MockupProvider;

use App\Form\NewsletterUnsubscribeType;
use Symfony\Component\Form\FormFactoryInterface;

class NewsletterUnsubscribeMockups implements MockupProviderInterface
{
    private const MOCKUPS = [
        'Newsletter (terms of use)' => [
            'type' => 'email',
            'template' => 'emails/newsletter.html.twig',
            'description' => 'Email send to users who signed up for local event notifications',
            'setup' => 'getNewsletterParameters',
        ],
        'Unsubscribe Newsletter' => [
            'type' => 'page',
            'url' => '/unsubscribe/newsletter/{username}/{token}',
            'template' => 'newsletter/unsubscribe_confirm.html.twig',
            'description' => 'Shown to a user when following the link in a regular newsletter',
            'setup' => 'getUnsubscribeParameters',
        ],
        'Unsubscribe Newsletter Success' => [
            'type' => 'page',
            'url' => '/unsubscribe/local/{username}/{token}',
            'template' => 'newsletter/unsubscribe_local_successful.html.twig',
            'description' => 'The page that is shown when a member unsubscribed without issues from a local event newsletter',
            'setup' => 'getUnsubscribeParameters',
        ],
        'Unsubscribe Newsletter Failed' => [
            'type' => 'page',
            'url' => '/unsubscribe/local/{username}/{token}',
            'template' => 'newsletter/unsubscribe_local_failed.html.twig',
            'description' => 'The page that is shown when a member unsubscribed with issues from a local event newsletter',
            'setup' => 'getUnsubscribeParameters',
        ],
        'Unsubscribe Local Event' => [
            'type' => 'page',
            'url' => '/unsubscribe/local/{username}/{token}',
            'template' => 'newsletter/unsubscribe_local_confirm.html.twig',
            'description' => 'Shown to a user when following the link in a local events newsletter',
            'setup' => 'getUnsubscribeParameters',
        ],
        'Unsubscribe Local Success' => [
            'type' => 'page',
            'url' => '/unsubscribe/local/{username}/{token}',
            'template' => 'newsletter/unsubscribe_local_successful.html.twig',
            'description' => 'The page that is shown when a member unsubscribed without issues from a local event newsletter',
            'setup' => 'getUnsubscribeParameters',
        ],
        'Unsubscribe Local Failed' => [
            'type' => 'page',
            'url' => '/unsubscribe/local/{username}/{token}',
            'template' => 'newsletter/unsubscribe_local_failed.html.twig',
            'description' => 'The page that is shown when a member unsubscribed with issues from a local event newsletter',
            'setup' => 'getUnsubscribeParameters',
        ],
    ];

    private FormFactoryInterface $formFactory;

    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    public function getFeature(): string
    {
        return 'unsubscribe_newsletter';
    }

    public function getMockups(): array
    {
        return self::MOCKUPS;
    }

    public function getMockupVariables(array $parameters): array
    {
        $unsubscribeForm = $this->formFactory->create(NewsletterUnsubscribeType::class);

        return [
            'username' => $parameters['user']->getUsername(),
            'form' => $unsubscribeForm->createView(),
        ];
    }

    public function getMockupParameter(?string $locale = null, ?string $feature = null): array
    {
        return [];
    }
}

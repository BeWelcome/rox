<?php

namespace App\Model\MockupProvider;

use App\Entity\ForumPost;
use App\Entity\ForumThread;
use Mockery;

class ForumMockups implements MockupProviderInterface
{
    private const MOCKUPS = [
        'forum post (subscribed)' => [
            'type' => 'email',
            'template' => 'emails/notifications.html.twig',
        ],
        'forum post (not subscribed)' => [
            'type' => 'email',
            'template' => 'emails/notifications.html.twig',
        ],
    ];

    public function getFeature(): string
    {
        return 'forums';
    }

    public function getMockups(): array
    {
        return self::MOCKUPS;
    }

    public function getMockupVariables(array $parameters): array
    {
        $mockThread = Mockery::mock(ForumThread::class, [
            'getId' => 1,
            'getGroup' => null,
            'getTitle' => 'Thread title',
        ]);

        $mockPost = Mockery::mock(ForumPost::class, [
            'getId' => 1,
            'getMessage' => 'Post text',
            'getThread' => $mockThread,
        ]);

        if ('forum post (subscribed)' === $parameters['name']) {
            $subscription = 123456;
        } else {
            $subscription = 0;
        }

        return [
            'notification' => [
                'post' => $mockPost,
                'subscription' => $subscription,
            ],
        ];
    }

    public function getMockupParameter(?string $locale = null, ?string $feature = null): array
    {
        return [];
    }
}

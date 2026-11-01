<?php

namespace App\Model\MockupProvider;

use App\Entity\ForumPost;
use App\Entity\ForumThread;

class ForumMockups implements MockupProviderInterface
{
    use MockupEntities;

    private const array MOCKUPS = [
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
        $thread = self::withId(new ForumThread(), 1)
            ->setTitle('Thread title');
        $post = self::withId(new ForumPost(), 1)
            ->setMessage('Post text')
            ->setThread($thread);

        if ('forum post (subscribed)' === $parameters['name']) {
            $subscription = 123456;
        } else {
            $subscription = 0;
        }

        return [
            'notification' => [
                'post' => $post,
                'subscription' => $subscription,
            ],
        ];
    }

    public function getMockupParameter(?string $locale = null, ?string $feature = null): array
    {
        return [];
    }
}

<?php

namespace App\Model\MockupProvider;

use App\Entity\ForumPost;
use App\Entity\ForumThread;

class ForumMockups extends AbstractMockups
{
    protected const string FEATURE = 'forums';

    protected const array MOCKUPS = [
        'forum post (subscribed)' => [
            'type' => 'email',
            'template' => 'emails/notifications.html.twig',
        ],
        'forum post (not subscribed)' => [
            'type' => 'email',
            'template' => 'emails/notifications.html.twig',
        ],
    ];

    public function getMockupVariables(array $parameters): array
    {
        $thread = (new ForumThread())
            ->setId(1)
            ->setGroup(null)
            ->setTitle('Thread title')
        ;

        $post = (new ForumPost())
            ->setMessage('Post text')
            ->setThread($thread)
        ;

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

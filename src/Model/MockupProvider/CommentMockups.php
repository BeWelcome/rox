<?php

namespace App\Model\MockupProvider;

use App\Doctrine\CommentQualityType;
use App\Entity\Comment;
use App\Entity\Member;
use Mockery;

class CommentMockups implements MockupProviderInterface
{
    private const MOCKUPS = [
        'new comment (notification)' => [
            'type' => 'email',
            'template' => 'emails/comment.notification.new.html.twig',
            'with_parameters' => true,
            'parameters' => [
                'quality' => [
                    'good' => CommentQualityType::POSITIVE,
                    'bad' => CommentQualityType::NEGATIVE,
                    'neutral' => CommentQualityType::NEUTRAL,
                ],
            ],
        ],
        'comment update (notification)' => [
            'type' => 'email',
            'template' => 'emails/comment.notification.update.html.twig',
            'with_parameters' => true,
            'parameters' => [
                'quality' => [
                    'good' => CommentQualityType::POSITIVE,
                    'bad' => CommentQualityType::NEGATIVE,
                    'neutral' => CommentQualityType::NEUTRAL,
                ],
            ],
        ],
        'comment reminder_guest (first)' => [
            'type' => 'email',
            'template' => 'emails/comment.first.reminder.guest.html.twig',
            'with_parameters' => false,
        ],
        'comment reminder_guest (second)' => [
            'type' => 'email',
            'template' => 'emails/comment.second.reminder.guest.html.twig',
            'with_parameters' => false,
        ],
        'comment reminder_host' => [
            'type' => 'email',
            'template' => 'emails/comment.reminder.host.html.twig',
            'with_parameters' => false,
        ],
    ];

    public function getFeature(): string
    {
        return 'comments';
    }

    public function getMockups(): array
    {
        return self::MOCKUPS;
    }

    public function getMockupParameter(?string $locale = null, ?string $feature = null): array
    {
        return [
            'quality' => [
                'good' => CommentQualityType::POSITIVE,
                'bad' => CommentQualityType::NEGATIVE,
                'neutral' => CommentQualityType::NEUTRAL,
            ],
        ];
    }

    public function getMockupVariables(array $parameters): array
    {
        $receiver = Mockery::mock(Member::class, [
            'getUsername' => 'Receiver',
        ]);
        $sender = Mockery::mock(Member::class, [
            'getUsername' => 'Sender',
        ]);
        $mockComment = Mockery::mock(Comment::class, [
            'getQuality' => $parameters['quality'] ?? CommentQualityType::NEUTRAL,
            'getTextWhere' => 'Somewhere over the rainbow',
            'getTextFree' => 'A comment.',
        ]);

        return [
            'comment' => $mockComment,
            'receiver' => $receiver,
            'sender' => $sender,
            'host' => 'host',
            'guest' => 'guest',
            'comment_start' => '<a href="/members/host/comment/add">',
            'comment_end' => '</a>',
            'profile_start' => '<a href="/members/host">',
            'profile_end' => '</a>',
        ];
    }
}

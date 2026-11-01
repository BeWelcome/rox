<?php

namespace App\Model\MockupProvider;

use App\Doctrine\CommentQualityType;
use App\Entity\Comment;

class CommentMockups implements MockupProviderInterface
{
    private const array MOCKUPS = [
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
        'comment feedback (volunteers)' => [
            'type' => 'email',
            'template' => 'emails/comment.feedback.html.twig',
            'with_parameters' => false,
            'description' => 'To the volunteers who check comments, when a member gives feedback on a comment (English only).',
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
        // stored members: the mails greet the receiver by name
        $receiver = $parameters['user'];
        $sender = $parameters['admin'];
        $comment = new Comment()
            ->setQuality($parameters['quality'] ?? CommentQualityType::NEUTRAL)
            ->setComment('A comment.')
            ->setFromMember($sender)
            ->setToMember($receiver);

        return [
            'comment' => $comment,
            'receiver' => $receiver,
            'sender' => $sender,
            'host' => 'host',
            'guest' => 'guest',
            'comment_start' => '<a href="/members/host/comment/add">',
            'comment_end' => '</a>',
            'profile_start' => '<a href="/members/host">',
            'profile_end' => '</a>',
            'report_start' => '<a href="/feedback?IdCategory=2&username=host">',
            'report_end' => '</a>',
            'feedback' => 'The comment is about another member, not about me.',
        ];
    }
}

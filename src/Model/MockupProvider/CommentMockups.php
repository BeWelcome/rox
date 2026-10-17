<?php

namespace App\Model\MockupProvider;

use App\Doctrine\CommentQualityType;
use App\Entity\Comment;
use App\Entity\Member;

class CommentMockups extends AbstractMockups
{
    protected const string FEATURE = 'comments';

    protected const array MOCKUPS = [
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
        $receiver = (new Member())->setUsername('Receiver');
        $sender = $parameters['user'];

        $comment = (new Comment())
            ->setTextWhere('Somewhere over the rainbow')
            ->setTextFree('A comment.')
            ->setQuality($parameters['quality'] ?? CommentQualityType::NEUTRAL)
        ;

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
        ];
    }
}

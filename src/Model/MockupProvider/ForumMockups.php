<?php

namespace App\Model\MockupProvider;

use App\Doctrine\GroupType;
use App\Doctrine\SubtripOptionsType;
use App\Doctrine\TripAdditionalInfoType;
use App\Entity\ForumPost;
use App\Entity\ForumThread;
use App\Entity\Group;
use App\Entity\HostingRequest;
use App\Entity\Location;
use App\Entity\Member;
use App\Entity\Message;
use App\Entity\Subject;
use App\Entity\Subtrip;
use App\Entity\Trip;
use App\Form\DataTransformer\DateTimeTransformer;
use App\Form\InvitationGuest;
use App\Form\InvitationHost;
use App\Form\InvitationType;
use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Symfony\Component\Form\FormFactoryInterface;

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

        if ($parameters['name'] === 'forum post (subscribed)') {
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

    public function getMockupParameter(): array
    {
        return [];
    }
}

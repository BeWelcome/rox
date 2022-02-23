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

class GroupMockups implements MockupProviderInterface
{
    private const MOCKUPS = [
        'group invitation' => [
            'type' => 'email',
            'template' => 'emails/group/invitation.html.twig',
        ],
        'group want in' => [
            'type' => 'email',
            'template' => 'emails/group/wantin.html.twig',
        ],
        'accepted invite' => [
            'type' => 'email',
            'template' => 'emails/group/accepted.invite.html.twig',
        ],
        'declined invite' => [
            'type' => 'email',
            'template' => 'emails/group/declined.invite.html.twig',
        ],
        'join approved' => [
            'type' => 'email',
            'template' => 'emails/group/join.approved.html.twig',
        ],
        'join declined' => [
            'type' => 'email',
            'template' => 'emails/group/join.declined.html.twig',
        ],
        'group post (subscribed)' => [
            'type' => 'email',
            'template' => 'emails/notifications.html.twig',
        ],
        'group post (not subscribed)' => [
            'type' => 'email',
            'template' => 'emails/notifications.html.twig',
        ],
    ];

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getFeature(): string
    {
        return 'groups';
    }

    public function getMockups(): array
    {
        return self::MOCKUPS;
    }

    public function getMockupVariables(array $parameters): array
    {
        // Use the first public group
        $groupRepository = $this->entityManager->getRepository(Group::class);
        $group = $groupRepository->findOneBy(
            [
                'type' => GroupType::PUBLIC,
                'approved' => Group::APPROVED
            ],
            ['created' => 'DESC']
        );

        /** @var Member $user */
        $user = $parameters['user'];
        $mockThread = Mockery::mock(ForumThread::class, [
            'getId' => 1,
            'getGroup' => $group,
            'getTitle' => 'Thread title',
        ]);

        $mockPost = Mockery::mock(ForumPost::class, [
            'getId' => 1,
            'getMessage' => 'Post text',
            'getThread' => $mockThread,
        ]);

        if ($parameters['name'] === 'group post (subscribed)') {
            $subscription = 123456;
        } else {
            $subscription = 0;
        }

        return [
            'group' => $group,
            'sender' => $parameters['admin'],
            'admin' => $parameters['admin'],
            'receiver' => $user,
            'subject' => 'group.invitation',
            'reason' => 'I just want to be a member of something.',
            'username' => $user->getUsername(),
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

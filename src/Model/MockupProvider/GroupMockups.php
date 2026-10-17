<?php

namespace App\Model\MockupProvider;

use App\Doctrine\GroupType;
use App\Entity\ForumPost;
use App\Entity\ForumThread;
use App\Entity\Group;
use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;

class GroupMockups extends AbstractMockups
{
    protected const string FEATURE = 'groups';

    protected const array MOCKUPS = [
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

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function getMockupVariables(array $parameters): array
    {
        // Use the first public group
        $groupRepository = $this->entityManager->getRepository(Group::class);
        $group = $groupRepository->findOneBy(
            [
                'type' => GroupType::PUBLIC,
                'approved' => Group::APPROVED,
            ],
            ['created' => 'DESC']
        );

        /** @var Member $user */
        $user = $parameters['user'];
        $thread = (new ForumThread())
            ->setId(1)
            ->setGroup($group)
            ->setTitle('Group thread title')
        ;

        $post = (new ForumPost())
            ->setId(1)
            ->setMessage('Post text')
            ->setThread($thread)
        ;

        if ('group post (subscribed)' === $parameters['name']) {
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

<?php

namespace AppBundle\Twig;

use AppBundle\Entity\Comment;
use AppBundle\Entity\Group;
use AppBundle\Entity\Member;
use AppBundle\Entity\Message;
use AppBundle\Repository\CommentRepository;
use AppBundle\Repository\MessageRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Security;
use Twig_Extension;
use Twig_Extension_GlobalsInterface;

class MemberTwigExtension extends Twig_Extension implements Twig_Extension_GlobalsInterface
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var Security
     */
    protected $security;

    /**
     * @var Member
     */
    protected $member;

    /**
     * MemberTwigExtension constructor.
     *
     * @param Session       $session
     * @param EntityManager $em
     * @param Router        $router
     * @param Security      $security
     */
    public function __construct(Session $session, EntityManager $em, Router $router, Security $security)
    {
        $this->session = $session;
        $this->em = $em;
        $this->router = $router;
        $this->security = $security;
        $this->member = $this->security->getUser();
    }

    /**
     * @return array
     */
    public function getGlobals()
    {
        $teams = [];
        if (null !== $this->member) {
            $roles = $this->security->getUser()->getRoles();
            $teams = $this->getTeams($roles);
        }

        return [
            'groupsInApprovalQueue' => $this->member ? $this->getGroupsInApprovalQueueCount() : 0,
            'reportedCommentsCount' => $this->member ? $this->getReportedCommentsCount() : 0,
            'reportedMessagesCount' => $this->member ? $this->getReportedMessagesCount() : 0,
            'messageCount' => $this->member ? $this->getUnreadMessagesCount() : 0,
            'requestCount' => $this->member ? $this->getUnreadRequestsCount() : 0,
            'teams' => $teams,
        ];
    }

    public function getName()
    {
        return self::class;
    }

    protected function getGroupsInApprovalQueueCount()
    {
        $groupsInApprovalCount = 0;
        $user = $this->security->getUser();
        if ($user &&
            ($this->security->isGranted(Member::ROLE_ADMIN_GROUP))) {
            $groupsRepository = $this->em->getRepository(Group::class);
            $groups = $groupsRepository->findBy([
                'approved' => [Group::NOT_APPROVED, Group::IN_DISCUSSION],
            ]);
            $groupsInApprovalCount = \count($groups);
        }

        return $groupsInApprovalCount;
    }

    protected function getReportedMessagesCount()
    {
        $reportedMessagesCount = 0;
        $user = $this->security->getUser();
        if ($user &&
            ($this->security->isGranted(Member::ROLE_ADMIN_CHECKER) ||
                $this->security->isGranted(Member::ROLE_ADMIN_SAFETYTEAM))) {
            /** @var MessageRepository $messageRepository */
            $messageRepository = $this->em->getRepository(Message::class);

            $reportedMessagesCount = $messageRepository->getReportedMessagesCount();
        }

        return $reportedMessagesCount;
    }

    protected function getReportedCommentsCount()
    {
        $reportedCommentsCount = 0;
        $user = $this->security->getUser();
        if ($user &&
            ($this->security->isGranted(Member::ROLE_ADMIN_CHECKER) ||
                $this->security->isGranted(Member::ROLE_ADMIN_SAFETYTEAM))) {
            /** @var CommentRepository $commentRepository */
            $commentRepository = $this->em->getRepository(Comment::class);
            $reportedCommentsCount = $commentRepository->getReportedCommentsCount();
        }

        return $reportedCommentsCount;
    }

    protected function getUnreadMessagesCount()
    {
        /** @var MessageRepository $messageRepository */
        $messageRepository = $this->em->getRepository(Message::class);

        return $messageRepository->getUnreadMessagesCount($this->member);
    }

    protected function getUnreadRequestsCount()
    {
        /** @var MessageRepository $messageRepository */
        $messageRepository = $this->em->getRepository(Message::class);

        return $messageRepository->getUnreadRequestsCount($this->member);
    }

    /**
     * @param array $roles
     *
     * @return array
     */
    protected function getTeams($roles)
    {
        $allTeams = [
            'communitynews' => [
                'trans' => 'AdminCommunityNews',
                'rights' => [Member::ROLE_ADMIN_COMMUNITYNEWS],
                'route' => 'admin_communitynews_overview',
            ],
            'words' => [
                'trans' => 'AdminWord',
                'rights' => [Member::ROLE_ADMIN_WORDS],
                'route' => 'translations',
            ],
            'flags' => [
                'trans' => 'AdminFlags',
                'rights' => [Member::ROLE_ADMIN_FLAGS],
                'route' => 'admin_flags',
            ],
            'rights' => [
                'trans' => 'AdminRights',
                'rights' => [Member::ROLE_ADMIN_RIGHTS],
                'route' => 'admin_rights',
            ],
            'logs' => [
                'trans' => 'AdminLogs',
                'rights' => [Member::ROLE_ADMIN_LOGS],
                'route' => 'admin_logs_overview',
            ],
            'comments' => [
                'trans' => 'AdminComments',
                'rights' => [Member::ROLE_ADMIN_SAFETYTEAM, Member::ROLE_ADMIN_COMMENTS],
                'route' => 'admin_comment_overview',
            ],
            'newmembersbewelcome' => [
                'trans' => 'AdminNewMembers',
                'rights' => [Member::ROLE_ADMIN_SAFETYTEAM, Member::ROLE_ADMIN_NEWMEMBERSBEWELCOME],
                'route' => 'newmembers',
            ],
            'massmail' => [
                'trans' => 'AdminMassMail',
                'rights' => [Member::ROLE_ADMIN_MASSMAIL],
                'route' => 'admin_massmail',
            ],
            'treasurer' => [
                'trans' => 'AdminTreasurer',
                'rights' => [Member::ROLE_ADMIN_TREASURER],
                'route' => 'admin_treasurer_overview',
            ],
            'faq' => [
                'trans' => 'AdminFAQ',
                'rights' => [Member::ROLE_ADMIN_FAQ],
                'route' => 'admin_faqs_overview',
            ],
            'group' => [
                'trans' => 'AdminGroup',
                'rights' => [Member::ROLE_ADMIN_GROUP],
                'route' => 'admin_groups_approval',
                'minimum_level' => 10,
            ],
            'tools' => [
                'trans' => 'AdminVolunteerTools',
                'rights' => [Member::ROLE_ADMIN_SAFETYTEAM, Member::ROLE_ADMIN_ADMIN,
                    Member::ROLE_ADMIN_SQLFORVOLUNTEERS, Member::ROLE_ADMIN_PROFILE,
                    Member::ROLE_ADMIN_CHECKER, Member::ROLE_ADMIN_ACCEPTER, ],
                'route' => 'admin_volunteer_tools',
            ],
        ];

        $teams = [];
        $assignedTeams = [];
        foreach ($allTeams as $name => $team) {
            foreach ($roles as $role) {
                if (!\in_array($name, $assignedTeams, true)) {
                    if (\in_array($role->getRole(), $team['rights'], true)) {
                        $add = true;
                        if (isset($team['minimum_level'])) {
                            $level = $this->member->getLevelForRight($role->getRole());
                            if ($level !== $team['minimum_level']) {
                                $add = false;
                            }
                        }
                        if ($add) {
                            $assignedTeams[] = $name;
                            $teams[] = [
                                'trans' => $team['trans'],
                                'route' => $team['route'],
                            ];
                        }
                    }
                }
            }
        }

        return $teams;
    }
}

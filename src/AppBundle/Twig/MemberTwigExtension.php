<?php

namespace AppBundle\Twig;

use AppBundle\Entity\Comment;
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
    }

    /**
     * @return array
     */
    public function getGlobals()
    {
        /** @var Member $member */
        $member = null;
        $rememberMeToken = unserialize($this->session->get('_security_default'));
        if (false !== $rememberMeToken) {
            $member = $rememberMeToken->getUser();
        }

        $teams = [];
        if (null !== $member) {
            $roles = $rememberMeToken->getRoles();
            $teams = $this->getTeams($roles);
        }

        return [
            'my_member' => $member ? $member : null,
            'reportedCommentsCount' => $member ? $this->getReportedCommentsCount() : 0,
            'reportedMessagesCount' => $member ? $this->getReportedMessagesCount() : 0,
            'messageCount' => $member ? $this->getUnreadMessagesCount($member) : 0,
            'requestCount' => $member ? $this->getUnreadRequestsCount($member) : 0,
            'teams' => $teams,
        ];
    }

    public function getName()
    {
        return self::class;
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

    protected function getUnreadMessagesCount(Member $member)
    {
        /** @var MessageRepository $messageRepository */
        $messageRepository = $this->em->getRepository(Message::class);

        return $messageRepository->getUnreadMessagesCount($member);
    }

    protected function getUnreadRequestsCount(Member $member)
    {
        /** @var MessageRepository $messageRepository */
        $messageRepository = $this->em->getRepository(Message::class);

        return $messageRepository->getUnreadRequestsCount($member);
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
                        $assignedTeams[] = $name;
                        $teams[] = [
                            'trans' => $team['trans'],
                            'route' => $team['route'],
                        ];
                    }
                }
            }
        }

        return $teams;
    }
}

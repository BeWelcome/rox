<?php

namespace AppBundle\Twig;

use AppBundle\Entity\Member;
use AppBundle\Entity\Message;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Session\Session;
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
     * MemberTwigExtension constructor.
     *
     * @param Session       $session
     * @param EntityManager $em
     * @param Router        $router
     */
    public function __construct(Session $session, EntityManager $em, Router $router)
    {
        $this->session = $session;
        $this->em = $em;
        $this->router = $router;
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
            'messageCount' => $member ? $this->getUnreadMessageCount($member) : 0,
            'requestCount' => $member ? $this->getUnreadRequestCount($member) : 0,
            'teams' => $teams,
        ];
    }

    public function getName()
    {
        return self::class;
    }

    protected function getUnreadMessageCount(Member $member)
    {
        $messageRepository = $this->em->getRepository(Message::class);

        return $messageRepository->getUnreadMessageCount($member);
    }

    protected function getUnreadRequestCount(Member $member)
    {
        $messageRepository = $this->em->getRepository(Message::class);

        return $messageRepository->getUnreadRequestCount($member);
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
                'route' => '/bw/admin/admincomments.php',
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
                if (!in_array($name, $assignedTeams, true)) {
                    if (in_array($role->getRole(), $team['rights'], true)) {
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

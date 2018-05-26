<?php

namespace AppBundle\Twig;

use AppBundle\Entity\Member;
use AppBundle\Entity\Message;
use Doctrine\ORM\EntityManager;
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
     * MemberTwigExtension constructor.
     *
     * @param Session       $session
     * @param EntityManager $em
     */
    public function __construct(Session $session, EntityManager $em)
    {
        $this->session = $session;
        $this->em = $em;
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
                'AdminCommunityNews',
                'admin/communitynews',
            ],
            'words' => [
                'AdminWord',
                'admin/word',
            ],
            'flags' => [
                'AdminFlags',
                'admin/flags',
            ],
            'rights' => [
                'AdminRights',
                'admin/rights',
            ],
            'logs' => [
                'AdminLogs',
                'admin/logs',
            ],
            'comments' => [
                'AdminComments',
                'bw/admin/admincomments.php',
            ],
            'newmembersbewelcome' => [
                'AdminNewMembers',
                'admin/newmembers',
            ],
            'massmail' => [
                'AdminMassMail',
                'admin/massmail',
            ],
            'treasurer' => [
                'AdminTreasurer',
                'admin/treasurer',
            ],
            'faq' => [
                'AdminFAQ',
                'admin/faqs/',
            ],
            'sqlforvolunteers' => [
                'AdminSqlForVolunteers',
                'bw/admin/adminquery.php',
            ],
        ];

        $teams = [];
        $keys = array_keys($allTeams);
        foreach ($roles as $role) {
            $role = strtolower(str_replace('ROLE_ADMIN_', '', $role->getRole()));
            if (in_array($role, $keys, true)) {
                $teams[] = [
                    'trans' => $allTeams[$role][0],
                    'link' => $allTeams[$role][1],
                ];
            }
        }

        return $teams;
    }
}

<?php

namespace AppBundle\Twig;

use AppBundle\Entity\Member;
use AppBundle\Entity\Message;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGenerator;
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
     * @param UrlGenerator  $router
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
                'AdminCommunityNews',
                'admin_communitynews_overview',
            ],
            'words' => [
                'AdminWord',
                'admin_word_overview',
            ],
            'flags' => [
                'AdminFlags',
                'admin_flags',
            ],
            'rights' => [
                'AdminRights',
                'admin_rights',
            ],
            'logs' => [
                'AdminLogs',
                'admin_logs_overview',
            ],
            'comments' => [
                'AdminComments',
                '/bw/admin/admincomments.php',
            ],
            'newmembersbewelcome' => [
                'AdminNewMembers',
                'newmembers',
            ],
            'massmail' => [
                'AdminMassMail',
                'admin_massmail',
            ],
            'treasurer' => [
                'AdminTreasurer',
                'admin_treasurer_overview',
            ],
            'faq' => [
                'AdminFAQ',
                'admin_faqs_overview',
            ],
            'sqlforvolunteers' => [
                'AdminSqlForVolunteers',
                '/bw/admin/adminquery.php',
            ],
        ];

        $teams = [];
        $keys = array_keys($allTeams);
        foreach ($roles as $role) {
            $role = strtolower(str_replace('ROLE_ADMIN_', '', $role->getRole()));
            if (in_array($role, $keys, true)) {
                $teams[] = [
                    'trans' => $allTeams[$role][0],
                    'route' => $allTeams[$role][1],
                ];
            }
        }

        return $teams;
    }
}

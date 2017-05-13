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
     *
     * @todo rename my_member to myMember for consistency
     */
    public function getGlobals()
    {
        $member = null;
        $rememberMeToken = unserialize($this->session->get('_security_default'));
        if ($rememberMeToken !== false) {
            $member = $rememberMeToken->getUser();
        }

        return [
            'messageCount' => $member ? $this->getUnreadMessagesCount($member) : 0,
            'teams' => $member ? $this->getTeams($member) : [],
        ];
    }

    public function getName()
    {
        return self::class;
    }

    protected function getUnreadMessagesCount(Member $member)
    {
        $messageRepository = $this->em->getRepository(Message::class);

        return $messageRepository->getUnreadCount($member);
    }

    /**
     * @todo The rights checking needs to be rewritten because it doesn't work
     *       with the Symfony login system.
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     *
     * @param Member $member
     *
     * @return array
     */
    protected function getTeams(Member $member)
    {
        $allTeams = [
            [
                'CommunityNews',
                'AdminCommunityNews',
                'admin/communitynews',
            ],
            [
                'Words',
                'AdminWord',
                'admin/word',
            ],
            [
                'Flags',
                'AdminFlags',
                'admin/flags',
            ],
            [
                'Rights',
                'AdminRights',
                'admin/rights',
            ],
            [
                'Logs',
                'AdminLogs',
                'admin/logs',
            ],
            [
                'Comments',
                'AdminComments',
                'bw/admin/admincomments.php',
            ],
            [
                'NewMembersBeWelcome',
                'AdminNewMembers',
                'admin/newmembers',
            ],
            [
                'MassMail',
                'AdminMassMail',
                'admin/massmail',
            ],
            [
                'Treasurer',
                'AdminTreasurer',
                'admin/treasurer',
            ],
            [
                'FAQ',
                'AdminFAQ',
                'bw/faq.php',
            ],
            [
                'SqlForVolunteers',
                'AdminSqlForVolunteers',
                'bw/admin/adminquery.php',
            ],
        ];

        $teams = [];

        $roles = $member->getRoles();
        foreach ($allTeams as $team) {
            if (array_search('ROLE_ADMIN_'.strtoupper($team[0]), $roles, true)) {
                $teams[] = [
                    'trans' => $team[1],
                    'link' => $team[2],
                ];
            }
        }

        return $teams;
    }
}

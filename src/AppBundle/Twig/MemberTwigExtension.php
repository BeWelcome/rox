<?php

namespace AppBundle\Twig;

use Illuminate\Database\Query\Expression;
use AppBundle\Entity\Member;
use AppBundle\Entity\Message;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig_Extension;
use Twig_Extension_GlobalsInterface;

class MemberTwigExtension extends Twig_Extension implements Twig_Extension_GlobalsInterface
{
    /**
     * @var Session
     */
    protected $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
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
        if ($rememberMeToken != null) {
            $member = $rememberMeToken->getUser();
        }

        return [
            'messageCount' => $member ? $this->getMessageCount($member) : 0,
            'teams' => $member ? $this->getTeams($member) : [],
        ];
    }

    public function getName()
    {
        return self::class;
    }

    protected function getMessageCount(Member $member)
    {
        $member;
/*        $message = new Message();

        $messageCount = $message->getConnection()->query()
            ->select([
                new Expression('COUNT(*) as cnt'),
            ])
            ->from($message->getTable())
            ->where('IdReceiver', (int) $member->id)
            ->where('WhenFirstRead', '0000-00-00 00:00')
            ->where('Status', 'Sent');

        return (int) $messageCount->value('cnt');
  */
        return 10;
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
            if (array_search('ROLE_ADMIN_' . strtoupper($team[0]), $roles, true)) {
                $teams[] = [
                    'trans' => $team[1],
                    'link' => $team[2],
                ];
            }
        }

        return $teams;
    }
}

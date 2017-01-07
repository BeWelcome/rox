<?php

namespace AppBundle\Twig;

use Illuminate\Database\Query\Expression;
use AppBundle\Entity\Member;
use AppBundle\Entity\Message;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig_Extension;
use Twig_Extension_GlobalsInterface;

class MemberTwigExtension extends Twig_Extension implements Twig_Extension_GlobalsInterface
{
    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @return array
     *
     * @todo rename my_member to myMember for consistency
     */
    public function getGlobals()
    {
        $member = $this->getMember();

        return [
            'my_member' => $member,
            'messageCount' => $member ? $this->getMessageCount($member) : null,
            'teams' => $member ? $this->getTeams($member) : [],
        ];
    }

    public function getName()
    {
        return self::class;
    }

    /**
     * @return Member|null
     */
    protected function getMember()
    {
        $token = $this->tokenStorage->getToken();

        if (!$token || $token instanceof AnonymousToken) {
            return;
        }

        return $token->getUser();
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

<?php

namespace Rox\Member\Extension;

use Illuminate\Database\Query\Expression;
use Rox\Member\Model\Member;
use Rox\Message\Model\Message;
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
        $message = new Message();

        $messageCount = $message->getConnection()->query()
            ->select([
                new Expression('COUNT(*) as cnt'),
            ])
            ->from($message->getTable())
            ->where('IdReceiver', (int) $member->id)
            ->where('WhenFirstRead', '0000-00-00 00:00')
            ->where('Status', 'Sent');

        return (int) $messageCount->value('cnt');
    }
}

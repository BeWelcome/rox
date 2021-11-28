<?php

namespace App\Security;

use App\Entity\Member;
use App\Entity\Message;
use App\Entity\User;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ConversationVoter extends Voter
{
    public const CONVERSATION_VIEW = 'CONVERSATION_VIEW';
    public const CONVERSATION_REPLY = 'CONVERSATION_REPLY';

    protected function supports(string $attribute, $subject): bool
    {
        if (!in_array($attribute, [self::CONVERSATION_VIEW, self::CONVERSATION_REPLY])) {
            return false;
        }

        if (!$subject instanceof Message) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        /** @var Member $member */
        $member = $token->getUser();

        if (!$member instanceof Member) {
            // the member must be logged in; if not, deny access
            return false;
        }

        /** @var Message $message */
        $message = $subject;

        switch ($attribute) {
            case self::CONVERSATION_VIEW:
                return $this->canView($message, $member);
            case self::CONVERSATION_REPLY:
                return $this->canReply($message, $member);
        }

        throw new LogicException('This code should not be reached!');
    }

    private function canView(Message $message, Member $member): bool
    {
        // if they can edit, they can view
        if ($this->canReply($message, $member)) {
            return true;
        }

        return false;
    }

    private function canReply(Message $message, Member $member): bool
    {
        // this assumes that the Post object has a `getOwner()` method
        return $member === $message->getReceiver() || $member === $message->getSender();
    }
}

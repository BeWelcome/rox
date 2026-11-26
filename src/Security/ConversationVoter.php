<?php

namespace App\Security;

use App\Entity\Member;
use App\Entity\Message;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ConversationVoter extends Voter
{
    public const CONVERSATION_VIEW = 'CONVERSATION_VIEW';
    public const CONVERSATION_REPLY = 'CONVERSATION_REPLY';

    protected function supports(string $attribute, $subject): bool
    {
        if (!\in_array($attribute, [self::CONVERSATION_VIEW, self::CONVERSATION_REPLY], true)) {
            return false;
        }

        if (!$subject instanceof Message) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        /** @var Member $member */
        $member = $token->getUser();

        if (!$member instanceof Member) {
            // the member must be logged in; if not, deny access
            return false;
        }

        /** @var Message $message */
        $message = $subject;

        return match ($attribute) {
            self::CONVERSATION_VIEW => $this->canView($message, $member),
            self::CONVERSATION_REPLY => $this->canReply($message, $member),
            default => throw new LogicException('This code should not be reached!'),
        };
    }

    private function canView(Message $message, Member $member): bool
    {
        return $member === $message->getReceiver() || $member === $message->getSender();
    }

    /**
     * A member of the conversation answers it while the other member can still read the answer: not one who left
     * (retired by data:retention too), was banned, taken out or suspended (Member::isVisibleToMembers()). Volunteers,
     * who may still see such a member, can't answer them either.
     */
    private function canReply(Message $message, Member $member): bool
    {
        if (!$this->canView($message, $member)) {
            return false;
        }
        $correspondent = $member === $message->getSender() ? $message->getReceiver() : $message->getSender();

        return $correspondent->isVisibleToMembers();
    }
}

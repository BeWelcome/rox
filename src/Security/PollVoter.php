<?php

namespace App\Security;

use App\Entity\Member;
use App\Entity\Poll;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Who may do what with a poll, as on the legacy polls pages:
 * - a draft is seen by its creator and by poll admins only; a poll limited to groups by their members,
 *   its creator and poll admins;
 * - the creator edits a poll while it is a draft, poll admins always;
 * - members vote once while the poll is open ("Ordered" polls never had voting);
 * - a vote can be taken back while the poll is open, if the poll allows it and is not anonymous
 *   (anonymous votes are not recorded per choice, so they can't be taken back);
 * - results: always if visible, after voting or once closed if visible after voting, else poll admins only.
 *
 * @extends Voter<string, Poll>
 */
class PollVoter extends Voter
{
    public const string VIEW = 'POLL_VIEW';
    public const string EDIT = 'POLL_EDIT';
    public const string VOTE = 'POLL_VOTE';
    public const string CANCEL_VOTE = 'POLL_CANCEL_VOTE';
    public const string RESULTS = 'POLL_RESULTS';

    public function __construct(private readonly AccessDecisionManagerInterface $accessDecisionManager)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $subject instanceof Poll
            && \in_array($attribute, [self::VIEW, self::EDIT, self::VOTE, self::CANCEL_VOTE, self::RESULTS], true);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $member = $token->getUser();
        if (!$member instanceof Member) {
            return false;
        }

        /** @var Poll $poll */
        $poll = $subject;
        $isAdmin = $this->accessDecisionManager->decide($token, [Member::ROLE_ADMIN_POLL]);
        $isCreator = $poll->getCreator() === $member;

        if (!$isAdmin && !$isCreator && ($poll->isDraft() || !$this->isInAllowedGroup($poll, $member))) {
            return false;
        }

        return match ($attribute) {
            self::VIEW => true,
            self::EDIT => $isAdmin || ($isCreator && $poll->isDraft()),
            self::VOTE => $poll->isOpen() && Poll::CHOICE_ORDERED !== $poll->getChoiceType() && !$this->hasVoted($poll, $member),
            self::CANCEL_VOTE => $poll->isOpen() && $poll->canChangeVote() && !$poll->isAnonymous() && $this->hasVoted($poll, $member),
            self::RESULTS => $this->canSeeResults($poll, $member, $isAdmin),
            default => false,
        };
    }

    private function canSeeResults(Poll $poll, Member $member, bool $isAdmin): bool
    {
        if ($isAdmin) {
            return true;
        }
        if ($poll->isDraft()) {
            return false;
        }

        return match ($poll->getResultsVisibility()) {
            Poll::RESULTS_VISIBLE => true,
            Poll::RESULTS_AFTER_VOTE => $poll->isClosed() || $this->hasVoted($poll, $member),
            default => false,
        };
    }

    private function isInAllowedGroup(Poll $poll, Member $member): bool
    {
        if (0 === $poll->getGroups()->count()) {
            return true;
        }
        $memberGroups = array_map(static fn ($group) => $group->getId(), $member->getGroups());
        foreach ($poll->getGroups() as $group) {
            if (\in_array($group->getId(), $memberGroups, true)) {
                return true;
            }
        }

        return false;
    }

    private function hasVoted(Poll $poll, Member $member): bool
    {
        return null !== $poll->getVoteOf($member);
    }
}

<?php

namespace App\Security;

use App\Entity\Activity;
use App\Entity\Member;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Who may do what with an activity, as on the legacy pages: organizers edit, cancel and reactivate it
 * until it is over; everyone can answer (yes, maybe, no) while it is neither over nor cancelled.
 *
 * @extends Voter<string, Activity>
 */
class ActivityVoter extends Voter
{
    public const string EDIT = 'ACTIVITY_EDIT';
    public const string CANCEL = 'ACTIVITY_CANCEL';
    public const string ATTEND = 'ACTIVITY_ATTEND';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return \in_array($attribute, [self::EDIT, self::CANCEL, self::ATTEND], true) && $subject instanceof Activity;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $member = $token->getUser();
        if (!$member instanceof Member) {
            return false;
        }

        /** @var Activity $activity */
        $activity = $subject;
        if ($activity->isOver()) {
            return false;
        }

        if (self::ATTEND === $attribute) {
            return !$activity->isCancelled();
        }

        return $activity->isOrganizer($member);
    }
}

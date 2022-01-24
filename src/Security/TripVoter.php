<?php

namespace App\Security;

use App\Entity\Member;
use App\Entity\Trip;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TripVoter extends Voter
{
    public const TRIP_VIEW = 'TRIP_VIEW';
    public const TRIP_EDIT = 'TRIP_EDIT';

    protected function supports(string $attribute, $subject): bool
    {
        if (!in_array($attribute, [self::TRIP_VIEW, self::TRIP_EDIT])) {
            return false;
        }

        if (!$subject instanceof Trip) {
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

        /** @var Trip */
        $trip = $subject;

        switch ($attribute) {
            case self::TRIP_VIEW:
                // Currently everyone can view any trip
                return true;
            case self::TRIP_EDIT:
                return $this->canEdit($trip, $member);
        }

        throw new LogicException('This code should not be reached!');
    }

    private function canReply(Trip $trip, Member $member): bool
    {
        // this assumes that the Post object has a `getOwner()` method
        return $member === $trip->getCreator();
    }
}

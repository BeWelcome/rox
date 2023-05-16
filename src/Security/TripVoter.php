<?php

namespace App\Security;

use App\Doctrine\SubtripOptionsType;
use App\Entity\Member;
use App\Entity\Trip;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TripVoter extends Voter
{
    public const TRIP_VIEW = 'TRIP_VIEW';
    public const TRIP_EDIT = 'TRIP_EDIT';
    public const TRIP_COPY = 'TRIP_COPY';
    public const TRIP_REMOVE = 'TRIP_REMOVE';

    protected function supports(string $attribute, $subject): bool
    {
        if (!\in_array($attribute, [self::TRIP_VIEW, self::TRIP_EDIT, self::TRIP_REMOVE], true)) {
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

        /** @var Trip $trip */
        $trip = $subject;
        if (null !== $trip->getDeleted()) {
            // A deleted trip can't be viewed or edited.
            return false;
        }

        if (self::TRIP_VIEW === $attribute) {
            // A trip can always been viewed by its creator
            if ($member === $trip->getCreator()) {
                return true;
            }

            // A trip that does not only consist of private legs can be viewed by everyone
            $view = false;
            foreach ($trip->getSubtrips() as $leg) {
                $view = $view || !in_array(SubtripOptionsType::PRIVATE, $leg->getOptions());
            }
            // excepts if it is expired
            $view = $view && !$trip->isExpired();

            return $view;
        }

        if (self::TRIP_REMOVE === $attribute || self::TRIP_COPY === $attribute) {
            return ($member === $trip->getCreator());
        }

        return $this->canEdit($trip, $member);
    }

    private function canEdit(Trip $trip, Member $member): bool
    {
        return ($member === $trip->getCreator() && !$trip->isExpired());
    }
}

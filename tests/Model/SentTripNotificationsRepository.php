<?php

namespace App\Tests\Model;

use App\Entity\MemberTripNotificationSent;
use Doctrine\ORM\EntityRepository;

class SentTripNotificationsRepository extends EntityRepository
{
    public array $finds = [];

    /**
     * @param MemberTripNotificationSent[] $existing
     */
    public function __construct(
        private readonly array $existing = [],
    ) {
    }

    public function findOneBy(array $criteria, ?array $orderBy = null): ?MemberTripNotificationSent
    {
        $this->finds[] = $criteria;

        foreach ($this->existing as $sent) {
            if ($sent->getMember() === $criteria['member'] && $sent->getTrip() === $criteria['trip']) {
                return $sent;
            }
        }

        return null;
    }
}

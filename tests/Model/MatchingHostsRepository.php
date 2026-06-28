<?php

namespace App\Tests\Model;

use App\Entity\Preference;
use App\Entity\Trip;
use Doctrine\ORM\EntityRepository;

class MatchingHostsRepository extends EntityRepository
{
    public array $calls = [];

    public function __construct(
        private readonly array $hosts,
    ) {
    }

    public function getMembersToNotifyAboutTrip(
        Trip $trip,
        array $notificationValues = [
            Preference::TRIP_NOTIFICATIONS_IMMEDIATELY,
        ],
        int $duration = 3,
    ): array {
        $this->calls[] = [$trip, $notificationValues, $duration];

        return $this->hosts;
    }
}

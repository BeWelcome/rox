<?php

namespace App\Tests\Model;

use App\Entity\Trip;
use Doctrine\ORM\EntityRepository;

class MatchingHostsRepository extends EntityRepository
{
    public function __construct(
        private readonly array $hosts,
    ) {
    }

    public function getMembersToNotifyAboutTrip(Trip $trip): array
    {
        return $this->hosts;
    }
}

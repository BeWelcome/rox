<?php

namespace App\Tests\Model;

use App\Entity\Trip;
use DateTimeInterface;
use Doctrine\ORM\EntityRepository;

class CreatedTripsRepository extends EntityRepository
{
    public ?DateTimeInterface $since = null;
    public ?DateTimeInterface $until = null;

    /**
     * @param Trip[] $trips
     */
    public function __construct(
        private readonly array $trips,
    ) {
    }

    public function findTripsCreatedBetween(DateTimeInterface $since, DateTimeInterface $until): array
    {
        $this->since = $since;
        $this->until = $until;

        return $this->trips;
    }
}

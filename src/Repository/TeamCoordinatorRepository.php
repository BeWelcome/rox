<?php

namespace App\Repository;

use App\Entity\TeamCoordinator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TeamCoordinator>
 */
class TeamCoordinatorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TeamCoordinator::class);
    }

    /**
     * @return array<string, TeamCoordinator> by team
     */
    public function byTeam(): array
    {
        $coordinators = [];
        foreach ($this->findAll() as $coordinator) {
            $coordinators[$coordinator->getTeam()] = $coordinator;
        }

        return $coordinators;
    }
}

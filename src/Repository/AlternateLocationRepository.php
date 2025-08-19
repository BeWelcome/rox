<?php

namespace App\Repository;

use App\Entity\AlternateLocation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AlternateLocation|null find($id, $lockMode = null, $lockVersion = null)
 * @method AlternateLocation|null findOneBy(array $criteria, array $orderBy = null)
 * @method AlternateLocation[] findAll()
 * @method AlternateLocation[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AlternateLocationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AlternateLocation::class);
    }
}

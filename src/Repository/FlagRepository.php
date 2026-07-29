<?php

namespace App\Repository;

use App\Entity\Flag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Flag>
 */
class FlagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Flag::class);
    }

    /**
     * @return Flag[]
     */
    public function findRelevant(): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.relevance > 0')
            ->orderBy('f.relevance', 'DESC')
            ->addOrderBy('f.name', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findOneByNameCaseInsensitive(string $name): ?Flag
    {
        return $this->createQueryBuilder('f')
            ->where('LOWER(f.name) = LOWER(:name)')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}

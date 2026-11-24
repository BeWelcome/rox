<?php

namespace App\Repository;

use App\Entity\SignupPattern;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SignupPattern>
 */
class SignupPatternRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SignupPattern::class);
    }

    /**
     * @return list<SignupPattern>
     */
    public function findActive(): array
    {
        return $this->findBy(['active' => true], ['id' => 'ASC']);
    }

    /**
     * @return list<SignupPattern> the active ones first, the newest first
     */
    public function findAllForList(): array
    {
        return $this->findBy([], ['active' => 'DESC', 'id' => 'DESC']);
    }
}

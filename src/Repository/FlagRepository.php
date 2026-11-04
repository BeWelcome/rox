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
     * The flag that closes writing in forums and groups; null where the database lacks it.
     */
    public function findForumPostingFlag(): ?Flag
    {
        return $this->findOneBy(['name' => Flag::NOT_ALLOWED_TO_POST_IN_FORUM]);
    }
}

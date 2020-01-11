<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class GroupRepository extends EntityRepository
{
    /**
     * Gets all groups which name uses any of the provided name parts.
     *
     * @param array $nameParts
     *
     * @return array
     */
    public function findByNameParts(array $nameParts)
    {
        $qb = $this->createQueryBuilder('g');
        for ($i = 0; $i < \count($nameParts); ++$i) {
            $qb->orWhere('g.name like :part' . $i)
                ->setParameter(':part' . $i, '%' . $nameParts[$i] . '%');
        }
        $qb->setMaxResults(12);

        return $qb
            ->getQuery()
            ->getResult();
    }
}

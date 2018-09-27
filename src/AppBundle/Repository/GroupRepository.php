<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class GroupRepository extends EntityRepository
{
    /**
     * Gets all groups which name uses any of the provided name parts.
     *
     * @param array $nameParts
     * @return array
     */
    public function findByNameParts(array $nameParts)
    {
        $qb = $this->createQueryBuilder('g');
        for($i = 0; $i < count($nameParts); $i++ )
        {
            $qb->orWhere('g.name like :part' . $i)
                ->setParameter(':part' . $i, '%' . $nameParts[$i] . '%');
        }
        $qb->setMaxResults(12);
        return $qb
            ->getQuery()
            ->getResult();
    }
}

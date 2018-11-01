<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

/**
 * This custom Doctrine repository contains some methods which are useful when
 * querying for blog post information.
 * See http://symfony.com/doc/current/book/doctrine.html#custom-repository-classes.
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class TripRepository extends EntityRepository
{
    /**
     * @return Query
     */
    public function queryLatest()
    {
        return $this->createQueryBuilder('t')
            ->where('t.createdAt <= :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery();
    }
}

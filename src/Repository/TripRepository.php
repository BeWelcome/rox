<?php

namespace App\Repository;

use App\Entity\Member;
use DateTime;
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
    public function queryTripsOfMember(Member $member)
    {
        return $this->createQueryBuilder('t')
            ->where('t.created <= :now')
            ->andWhere('t.creator = :creator')
            ->setParameter(':now', new DateTime())
            ->setParameter(':creator', $member)
            ->orderBy('t.created', 'DESC')
            ->getQuery();
    }
}

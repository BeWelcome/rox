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
     * @param mixed $id
     * @param null  $lockMode
     * @param null  $lockVersion
     *
     * @return Trip|null
     */
    public function find($id, $lockMode = null, $lockVersion = null)
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.subtrips', 's')
            ->leftJoin('s.location', 'l')
            ->addSelect('s', 'l')
            ->where('t.id = :id')
            ->setParameter(':id', $id)
            ->addOrderBy('s.arrival', 'ASC')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return Query
     */
    public function queryTripsOfMember(Member $member)
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.subtrips', 's')
            ->leftJoin('s.location', 'l')
            ->addSelect('s', 'l')
            ->where('t.created <= :now')
            ->andWhere('t.creator = :creator')
            ->andWhere('t.deleted IS NULL')
            ->setParameter(':now', new DateTime())
            ->setParameter(':creator', $member)
            ->orderBy('t.created', 'DESC')
            ->addOrderBy('s.arrival', 'ASC')
            ->getQuery();
    }
}

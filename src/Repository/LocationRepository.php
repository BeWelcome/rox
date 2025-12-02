<?php

namespace App\Repository;

use App\Entity\Location;
use Doctrine\ORM\EntityRepository;

class LocationRepository extends EntityRepository
{
    public function findCountry(string $country): ?Location
    {
        $qb = $this->createQueryBuilder('l');
        $query =
            $qb
                ->select('l')
                ->where($qb->expr()->eq('l.admin1', ':admin1'))
                ->andWhere($qb->expr()->eq('l.country', ':country'))
                ->andWhere($qb->expr()->eq('l.fclass', ':fclass'))
                ->andWhere($qb->expr()->eq('l.fcode', ':fcode'))
                ->setParameter('country', $country)
                ->setParameter('fclass', 'P')
                ->setParameter('fcode', 'PCL')
                ->getQuery()
        ;

        return $query->getOneOrNullResult();
    }

    /**
     * Gets admin unit 1 for a given admin1 and country.
     */
    public function findAdminUnit(string $admin1, string $country): ?Location
    {
        $qb = $this->createQueryBuilder('l');
        $query =
            $qb
                ->select('l')
                ->where($qb->expr()->eq('l.admin1', ':admin1'))
                ->andWhere($qb->expr()->eq('l.country', ':country'))
                ->andWhere($qb->expr()->eq('l.fclass', ':fclass'))
                ->andWhere($qb->expr()->eq('l.fcode', ':fcode'))
                ->setParameter('admin1', $admin1)
                ->setParameter('country', $country)
                ->setParameter('fclass', 'A')
                ->setParameter('fcode', 'ADM1')
                ->getQuery()
        ;

        return $query->getOneOrNullResult();
    }
}

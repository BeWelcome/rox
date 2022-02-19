<?php

namespace App\Repository;

use App\Doctrine\MemberStatusType;
use App\Entity\Member;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;

class LocationRepository extends EntityRepository
{
    /**
     * Gets admin unit 1 for a given admin1 and country.
     *
     * @return mixed
     */
    public function findAdminUnit(string $admin1, string $country)
    {
        $qb = $this->createQueryBuilder('l');
        $query =
            $qb
                ->select('l')
                ->where($qb->expr()->eq('l.admin1', ':admin1'))
                ->andWhere($qb->expr()->eq('l.country', ':country'))
                ->andWhere($qb->expr()->eq('l.fclass', ':fclass'))
                ->andWhere($qb->expr()->eq('l.fcode', ':fcode'))
                ->setParameter(':admin1', $admin1)
                ->setParameter(':country', $country)
                ->setParameter(':fclass', 'A')
                ->setParameter(':fcode', 'ADM1')
                ->getQuery()
        ;

        return $query->getOneOrNullResult();
    }
}

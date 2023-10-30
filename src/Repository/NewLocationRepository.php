<?php

namespace App\Repository;

use App\Entity\NewLocation;
use Doctrine\ORM\EntityRepository;

class NewLocationRepository extends EntityRepository
{
    public function findCountry(string $countryId): ?NewLocation
    {
        $qb = $this->createQueryBuilder('l');
        $query =
            $qb
                ->select('l')
                ->where($qb->expr()->eq('l.countryId', ':countryId'))
                ->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->andX(
                            $qb->expr()->eq('l.featureClass', $qb->expr()->literal('A')),
                            $qb->expr()->like('l.featureCode', $qb->expr()->literal('PCL%')),
                            $qb->expr()->neq('l.featureCode', $qb->expr()->literal('PRSH')),
                            $qb->expr()->neq('l.featureCode', $qb->expr()->literal('PCLH')),
                        ),
                        $qb->expr()->like('l.featureCode', $qb->expr()->literal('TERR'))
                    )
                )
                ->setParameter(':countryId', $countryId)
                ->getQuery()
        ;

        return $query->getOneOrNullResult();
    }
}

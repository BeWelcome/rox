<?php

namespace App\Repository;

use App\Entity\Donation;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

/**
 * @extends EntityRepository<Donation>
 */
class DonationRepository extends EntityRepository
{
    /**
     * All donations, the newest first.
     */
    public function getDonationListQuery(): Query
    {
        return $this->createQueryBuilder('d')
            ->orderBy('d.created', 'DESC')
            ->addOrderBy('d.id', 'DESC')
            ->getQuery();
    }
}

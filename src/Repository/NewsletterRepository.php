<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class NewsletterRepository extends EntityRepository
{
    public function findAllPublished()
    {
        $qb = $this->createQueryBuilder('n')
            ->where("n.status = 'Triggered'")
            ->orderBy('n.created', 'DESC')
        ;

        $query = $qb->getQuery();

        return $query->getResult();
    }
}

<?php

namespace App\Repository;

use App\Entity\Newsletter;
use Doctrine\ORM\EntityRepository;

class NewsletterRepository extends EntityRepository
{
    public function findAllPublished()
    {
        $qb = $this->createQueryBuilder('n')
            ->where("n.status = 'Triggered'")
            ->andWhere('n.type = :type')
            ->setParameter(':type', Newsletter::REGULAR_NEWSLETTER)
            ->orderBy('n.created', 'DESC')
        ;

        $query = $qb->getQuery();

        return $query->getResult();
    }
}

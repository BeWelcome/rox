<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Member;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class LogRepository extends EntityRepository
{
    /**
     * Returns a Pagerfanta object encapsulating the matching paginated log messages.
     *
     * @param array       $types
     * @param Member|null $member
     * @param int         $page
     * @param int         $limit
     *
     * @return Pagerfanta
     */
    public function findLatest(array $types, Member $member = null, $page, $limit)
    {
        $paginator = new Pagerfanta(new DoctrineORMAdapter($this->queryLatest($types, $member)));
        $paginator->setMaxPerPage($limit);
        $paginator->setCurrentPage($page);

        return $paginator;
    }

    /**
     * @param array  $types
     * @param Member $member
     *
     * @return Query
     */
    private function queryLatest(array $types, Member $member = null)
    {
        $qb = $this->createQueryBuilder('l');
        if (!empty($types)) {
            $qb
                ->andWhere('l.type in (:types)')
                ->setParameter(':types', $types);
        }

        if ($member) {
            $qb
                ->andWhere('l.member = :member')
                ->setParameter(':member', $member);
        }
        $qb->orderBy('l.created', 'DESC');

        return $qb->getQuery();
    }
}

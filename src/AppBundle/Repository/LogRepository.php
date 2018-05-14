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
     * @param string      $ipAddress
     * @param int         $page
     * @param int         $limit
     *
     * @return Pagerfanta
     */
    public function findLatest(array $types, Member $member = null, $ipAddress, $page, $limit)
    {
        $paginator = new Pagerfanta(new DoctrineORMAdapter($this->queryLatest($types, $member, $ipAddress)));
        $paginator->setMaxPerPage($limit);
        $paginator->setCurrentPage($page);

        return $paginator;
    }

    /**
     * @param array  $types
     * @param Member $member
     * @param string $ipAddress
     *
     * @return Query
     */
    private function queryLatest(array $types, Member $member = null, $ipAddress)
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

        if ($ipAddress) {
            $qb
                ->andWhere('l.ipAddress = :ipAddress')
                ->setParameter(':ipAddress', ip2long($ipAddress));
        }

        return $qb->getQuery();
    }
}

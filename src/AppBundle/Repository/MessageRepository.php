<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Member;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class MessageRepository extends EntityRepository
{
    /**
     * @param Member $member
     * @param $filter
     * @param $sort
     * @param $sortDirection
     *
     * @return Query
     */
    public function queryLatest(Member $member, $filter, $sort, $sortDirection)
    {
        $qb = $this->createQueryBuilder('m');
        if ($filter === 'sent') {
            $qb->where('m.sender = :member');
        } else {
            $qb->where('m.receiver = :member');
        }
        $qb->setParameter('member', $member);
        switch ($filter) {
            case 'inbox':
                $qb->andWhere('m.infolder = :filter')
                   ->setParameter('filter', 'normal');
                break;
            case 'sent':
            case 'spam':
            case 'requests':
                $qb->andWhere('m.infolder = :filter')
                    ->setParameter('filter', $filter);
                break;
            case 'deleted':
                $qb->andWhere('m.deleterequest LIKE :deleterequest ')
                    ->setParameter('deleterequest', 'receiverdeleted');
        }
        $qb->orderBy('m.'.$sort, $sortDirection);

        return $qb->getQuery();
    }

    /**
     * Returns a Pagerfanta object encapsulating the matching paginated activities.
     *
     * @param Member $member
     * @param $filter
     * @param $sort
     * @param $sortDirection
     * @param int $page
     * @param int $items
     *
     * @return Pagerfanta
     */
    public function findLatest(Member $member, $filter, $sort, $sortDirection, $page = 1, $items = 10)
    {
        $paginator = new Pagerfanta(new DoctrineORMAdapter($this->queryLatest($member, $filter, $sort, $sortDirection), false));
        $paginator->setMaxPerPage($items);
        $paginator->setCurrentPage($page);

        return $paginator;
    }
}

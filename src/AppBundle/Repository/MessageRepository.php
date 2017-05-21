<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Member;
use AppBundle\Entity\Message;
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
            case 'requests':
                $qb->andWhere('m.infolder = :filter')
                    ->setParameter('filter', 'request');
                break;
            case 'sent':
            case 'spam':
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

    public function getUnreadCount(Member $member)
    {
        $qb = $this->createQueryBuilder('m');

        return $qb->select('count(m.id)')
            ->where('m.receiver = :member')
            ->setParameter('member', $member->getId())
            ->andWhere('NOT (m.deleterequest LIKE :receiverDeleted)')
            ->setParameter('receiverDeleted', 'receiverdeleted')
            ->andWhere('m.whenfirstread = :whenFirstRead')
            ->setParameter('whenFirstRead', '0000-00-00 00:00:00')
            ->getQuery()
            ->getSingleScalarResult();
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

    public function getThread(Message $message)
    {
        $qb = $this->createNativeNamedQuery('get_thread')
            ->setHint('partial', Query::HINT_FORCE_PARTIAL_LOAD);
        $result = $qb->execute([
            'message_id' => $message->getId()
            ]);
        return $result;

    }
}

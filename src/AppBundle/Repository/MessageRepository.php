<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Member;
use AppBundle\Entity\Message;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class MessageRepository extends EntityRepository
{
    /**
     * @param Member $member
     * @param $folder
     * @param $sort
     * @param $sortDirection
     *
     * @return QueryBuilder
     */
    public function queryLatestMessages(Member $member, $folder, $sort, $sortDirection)
    {
        if ('date' === $sort) {
            $sort = 'created';
        }
        $qb = $this->createQueryBuilder('m');
        if ('sent' === $folder) {
            $qb->where('m.sender = :member');
        } else {
            $qb->where('m.receiver = :member');
        }
        $qb
            ->setParameter('member', $member)
            ->andWhere('m.request IS NULL');
        switch ($folder) {
            case 'inbox':
                $qb->andWhere('NOT(m.deleteRequest LIKE :deleterequest)')
                    ->setParameter('deleterequest', 'receiverdeleted')
                    ->andWhere('m.infolder = :folder')
                    ->setParameter('folder', 'normal');
                break;
            case 'spam':
                $qb->andWhere('NOT(m.deleteRequest LIKE :deleterequest)')
                    ->setParameter('deleterequest', 'receiverdeleted')
                    ->andWhere('m.infolder = :folder')
                    ->setParameter('folder', $folder);
                break;
            case 'deleted':
                $qb->andWhere('m.deleteRequest LIKE :deleterequest ')
                    ->setParameter('deleterequest', 'receiverdeleted');
                break;
        }
        $qb->orderBy('m.'.$sort, $sortDirection);

        return $qb;
    }

    /**
     * @param Member $member
     * @param $folder
     * @param $sort
     * @param $sortDirection
     *
     * @return QueryBuilder
     */
    public function queryLatestRequests(Member $member, $folder, $sort, $sortDirection)
    {
        if ('date' === $sort) {
            $sort = 'created';
        }
        $qb = $this->createQueryBuilder('m');
        if ('sent' === $folder) {
            $qb->where('m.sender = :member');
        } else {
            $qb->where('m.receiver = :member');
        }
        $qb
            ->setParameter('member', $member)
            ->join('m.request', 'r')
            ->orderBy('m.'.$sort, $sortDirection);

        return $qb;
    }

    /**
     * @param Member $member
     * @param $folder
     * @param $sort
     * @param $sortDirection
     *
     * @return QueryBuilder
     */
    public function queryLatestRequestsAndMessages(Member $member, $folder, $sort, $sortDirection)
    {
        if ('date' === $sort) {
            $sort = 'created';
        }
        $qb = $this->createQueryBuilder('m');
        if ('sent' === $folder) {
            $qb->where('m.sender = :member');
        } else {
            $qb->where('m.receiver = :member');
        }
        $qb
            ->setParameter('member', $member)
            ->orderBy('m.'.$sort, $sortDirection);

        return $qb;
    }

    /**
     * @param Member $member
     *
     * @return mixed|null
     */
    public function getUnreadMessageCount(Member $member)
    {
        $q = $this->createQueryBuilder('m')
            ->select('count(m.id)')
            ->where('m.receiver = :member')
            ->setParameter('member', $member->getId())
            ->andWhere('NOT (m.deleteRequest LIKE :receiverDeleted)')
            ->setParameter('receiverDeleted', 'receiverdeleted')
            ->andWhere('m.whenfirstread = :whenFirstRead')
            ->setParameter('whenFirstRead', '0000-00-00 00:00:00')
            ->andWhere('m.status = :status')
            ->setParameter('status', 'Sent')
            ->andWhere('m.request IS NULL')
            ->getQuery();

        $results = null;
        try {
            $results = $q->getSingleScalarResult();
        } catch (NonUniqueResultException $e) {
        }

        return $results;
    }

    /**
     * @param Member $member
     *
     * @return mixed|null
     */
    public function getUnreadRequestCount(Member $member)
    {
        $q = $this->createQueryBuilder('m')
            ->join('m.request', 'r')
            ->select('count(m.id)')
            ->where('m.receiver = :member')
            ->setParameter('member', $member->getId())
            ->andWhere('NOT (m.deleteRequest LIKE :receiverDeleted)')
            ->setParameter('receiverDeleted', 'receiverdeleted')
            ->andWhere('m.whenfirstread = :whenFirstRead')
            ->setParameter('whenFirstRead', '0000-00-00 00:00:00')
            ->andWhere('m.status = :status')
            ->setParameter('status', 'Sent')
            ->getQuery();

        $results = null;
        try {
            $results = $q->getSingleScalarResult();
        } catch (NonUniqueResultException $e) {
        }

        return $results;
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
    public function findLatestMessages(Member $member, $filter, $sort, $sortDirection, $page = 1, $items = 10)
    {
        $queryBuilder = $this->queryLatestMessages($member, $filter, $sort, $sortDirection);
        $adapter = new DoctrineORMAdapter($queryBuilder);
        $paginator = new Pagerfanta($adapter);
        $paginator->setMaxPerPage($items);
        $paginator->setCurrentPage($page);

        return $paginator;
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
    public function findLatestRequests(Member $member, $filter, $sort, $sortDirection, $page = 1, $items = 10)
    {
        $paginator = new Pagerfanta(new DoctrineORMAdapter($this->queryLatestRequests($member, $filter, $sort, $sortDirection), false));
        $paginator->setMaxPerPage($items);
        $paginator->setCurrentPage($page);

        return $paginator;
    }

    /**
     * Returns a Pagerfanta object encapsulating the matching paginated activities.
     *
     * @param Member $member
     * @param $url
     * @param $filter
     * @param $sort
     * @param $sortDirection
     * @param int $page
     * @param int $items
     *
     * @return Pagerfanta
     */
    public function findLatestRequestsAndMessages(Member $member, $filter, $sort, $sortDirection, $page = 1, $items = 10)
    {
        $paginator = new Pagerfanta(new DoctrineORMAdapter($this->queryLatestRequestsAndMessages($member, $filter, $sort, $sortDirection), false));
        $paginator->setMaxPerPage($items);
        $paginator->setCurrentPage($page);

        return $paginator;
    }

    public function getThread(Message $message)
    {
        $qb = $this->createNativeNamedQuery('get_thread')
            ->setHint('partial', Query::HINT_FORCE_PARTIAL_LOAD);
        $result = $qb->execute([
            'message_id' => $message->getId(),
            ]);

        return $result;
    }
}

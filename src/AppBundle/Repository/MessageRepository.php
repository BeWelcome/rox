<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Member;
use AppBundle\Entity\Message;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Rox\Core\Exception\InvalidArgumentException;

class MessageRepository extends EntityRepository
{
    const MESSAGES_ONLY = 1;
    const REQUESTS_ONLY = 2;
    const MESSAGES_AND_REQUESTS = 3;

    /**
     * @param Member $member
     * @param $folder
     * @param $sort
     * @param $sortDirection
     * @param mixed $type
     *
     * @return Query
     */
    public function queryLatest(Member $member, $type, $folder, $sort, $sortDirection)
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
        $qb->setParameter('member', $member);
        switch ($type) {
            case self::MESSAGES_ONLY:
                $qb->andWhere('m.request IS NULL');
                break;
            case self::REQUESTS_ONLY:
                $qb->join('m.request', 'r');
                $folder = 'requests';
                break;
            case self::MESSAGES_AND_REQUESTS:
                // Nothing to do here
                break;
        }
        switch ($folder) {
            case 'inbox':
                $qb->andWhere('m.infolder = :folder')
                    ->setParameter('folder', 'normal');
                break;
            case 'spam':
                $qb->andWhere('m.infolder = :folder')
                    ->setParameter('folder', $folder);
                break;
            case 'deleted':
                $qb->andWhere('m.deleterequest LIKE :deleterequest ')
                    ->setParameter('deleterequest', 'receiverdeleted');
                break;
        }
        $qb->orderBy('m.'.$sort, $sortDirection);

        return $qb->getQuery();
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
            ->andWhere('NOT (m.deleterequest LIKE :receiverDeleted)')
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
            ->andWhere('NOT (m.deleterequest LIKE :receiverDeleted)')
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
     * @param $url
     * @param $filter
     * @param $sort
     * @param $sortDirection
     * @param int $page
     * @param int $items
     *
     * @return Pagerfanta
     */
    public function findLatest(Member $member, $url, $filter, $sort, $sortDirection, $page = 1, $items = 10)
    {
        switch ($url) {
            case 'both':
                $paginator = new Pagerfanta(new DoctrineORMAdapter($this->queryLatest($member, self::MESSAGES_AND_REQUESTS, $filter, $sort, $sortDirection), false));
                break;
            case 'requests':
                $paginator = new Pagerfanta(new DoctrineORMAdapter($this->queryLatest($member, self::REQUESTS_ONLY, $filter, $sort, $sortDirection), false));
                break;
            case 'messages':
                $paginator = new Pagerfanta(new DoctrineORMAdapter($this->queryLatest($member, self::MESSAGES_ONLY, $filter, $sort, $sortDirection), false));
                break;
            default:
                throw new InvalidArgumentException('Wrong type for message repository');
        }
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

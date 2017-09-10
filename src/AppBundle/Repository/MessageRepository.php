<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Member;
use AppBundle\Entity\Message;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Mockery\Exception;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Rox\Core\Exception\InvalidArgumentException;
use Symfony\Bundle\MonologBundle\MonologBundle;

class MessageRepository extends EntityRepository
{
    /**
     * @param Member $member
     * @param $folder
     * @param $sort
     * @param $sortDirection
     *
     * @return Query
     */
    public function queryLatest(Member $member, $folder, $sort, $sortDirection)
    {
        if ($sort == 'date') {
            $sort = 'created';
        }
        $qb = $this->createQueryBuilder('m');
        if ($folder === 'sent') {
            $qb->where('m.sender = :member');
        } else {
            $qb->where('m.receiver = :member');
        }
        $qb->setParameter('member', $member);
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
     * @param $folder
     * @param $sort
     * @param $sortDirection
     *
     * @return Query
     */
    public function queryRequests(Member $member, $folder, $sort, $sortDirection)
    {
        if ($sort == 'datesent') {
            $sort = 'created';
        }
        $qb = $this->createQueryBuilder('m');
        switch($folder) {
            case 'inbox':
                $qb->where('m.receiver = :member');
                break;
            case 'sent':
                $qb->where('m.sender = :member');
                break;
            default:
                throw new InvalidArgumentException('Wrong folder type');
        }
        $qb ->join('m.request', 'r')
            ->setParameter('member', $member)
            ->orderBy('m.'.$sort, $sortDirection);

        return $qb->getQuery();
    }

    /**
     * @param Member $member
     * @return mixed|null
     */
    public function getUnreadCount(Member $member)
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
            ->getQuery();

        $results = null;
        try{
            $results = $q->getSingleScalarResult();
        }
        catch(NonUniqueResultException $e)
        {
        }
        catch(NoResultException $e)
        {
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
    public function findLatest(Member $member, $filter, $sort, $sortDirection, $page = 1, $items = 10)
    {
        list($type, $folder) = explode('_', $filter);
        switch($type)
        {
            case 'requests':
                $paginator = new Pagerfanta(new DoctrineORMAdapter($this->queryRequests($member, $folder, $sort, $sortDirection), false));
                break;
            case 'messages':
                $paginator = new Pagerfanta(new DoctrineORMAdapter($this->queryLatest($member, $folder, $sort, $sortDirection), false));
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

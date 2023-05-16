<?php

namespace App\Repository;

use App\Entity\Member;
use Doctrine\ORM\EntityRepository;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Doctrine\Collections\CollectionAdapter;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

class ProfileVisitRepository extends EntityRepository
{
    public function getProfileVisitorsMember(Member $member, int $page): Pagerfanta
    {
        // $profileVisitors = $this->findby(['member' => $member], ['updated' => 'DESC']);
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('p')
            ->from('App:ProfileVisit', 'p')
            ->join('p.member', 'm', 'WITH', 'm.id = :memberId')
            ->join('p.visitor', 'v', 'WITH', 'v.status IN (:status)')
            ->orderBy('p.updated', 'DESC')
            ->setParameter(':memberId', $member->getId())
            ->setParameter(':status', ['Active', 'OutOfRemind'])
        ;
        $result = $qb->getQuery()->getResult();

        $paginator = new Pagerfanta(new ArrayAdapter($result));
        $paginator->setMaxPerPage(20);
        $paginator->setCurrentPage($page);

        return $paginator;
    }
}

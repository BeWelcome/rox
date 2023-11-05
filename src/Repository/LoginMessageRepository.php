<?php

namespace App\Repository;

use App\Entity\Member;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\ResultSetMapping;

class LoginMessageRepository extends EntityRepository
{
    /**
     * Gets open login messages for member.
     *
     * @return mixed
     */
    public function getLoginMessages(Member $member)
    {
        $qb = $this->createQueryBuilder('lm');
        $query = $qb
            ->leftJoin(
                'App:LoginMessageAcknowledged',
                'lma',
                Join::WITH,
                'lma.message = lm AND lma.member = :member'
            )
            ->where($qb->expr()->isNull('lma.message'))
            ->andWhere($qb->expr()->gt('lm.expires', $qb->expr()->literal('now()')))
            ->setParameter(':member', $member->getId())
            ->getQuery()
        ;
        return  $query->getResult();
    }
}

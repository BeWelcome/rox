<?php

namespace App\Repository;

use App\Entity\LoginMessageAcknowledged;
use App\Entity\NewMember as Member;
use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

class LoginMessageRepository extends EntityRepository
{
    /**
     * Gets open login messages for member.
     */
    public function getLoginMessages(Member $member): mixed
    {
        $qb = $this->createQueryBuilder('lm');
        $query = $qb
            ->leftJoin(
                LoginMessageAcknowledged::class,
                'lma',
                Join::WITH,
                'lma.message = lm AND lma.member = :member'
            )
            ->where($qb->expr()->isNull('lma.message'))
            ->andWhere($qb->expr()->gt('lm.expires', ':now'))
            ->setParameter('now', new DateTime())
            ->setParameter('member', $member->getId())
            ->getQuery()
        ;

        return $query->getResult();
    }
}

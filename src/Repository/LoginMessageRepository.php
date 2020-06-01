<?php

namespace App\Repository;

use App\Entity\Member;
use Doctrine\ORM\EntityRepository;
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
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult('App:LoginMessage', 'lm');
        $rsm->addFieldResult('lm', 'id', 'id');
        $rsm->addFieldResult('lm', 'text', 'text');
        $rsm->addFieldResult('lm', 'created', 'created');
        $query = $this->getEntityManager()
            ->createNativeQuery('
                SELECT lm.id, lm.text, lm.created FROM login_messages lm
                LEFT JOIN `login_messages_acknowledged` lma ON lm.id = lma.messageId AND lma.memberId = :memberId
                WHERE
                    lma.messageId IS NULL
                    AND (lm.created > (NOW() - INTERVAL 1 MONTH))
                ORDER BY
                    lm.created ASC                
            ', $rsm)
            ->setParameter('memberId', $member->getId())
            ;
        $result = $query
            ->getResult();

        return $result;
    }
}

<?php

namespace App\Pagerfanta;

use App\Entity\Member;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;
use Pagerfanta\Adapter\AdapterInterface;

class RequestsAdapter extends AbstractConversationsAdapter implements AdapterInterface
{
    protected function getSqlQueryTemplate(): string
    {
        $sql = '
            SELECT %select%
            FROM `messages` m
            LEFT JOIN `request` r ON `m`.`request_id` = `r`.`id`
            WHERE '
            . $this->getInitiatorCondition() . '
            AND ' . $this->getUnreadCondition() . '
            AND `m`.`request_id` = `r`.`id`
            AND `r`.`invite_for_leg` IS NULL
            AND `m`.`id` IN (
                    SELECT max(`m`.`id`)
                    FROM `messages` m
                    GROUP BY `m`.`subject_id`
                )
            AND ' . $this->getNotDeletedOrPurgedCondition() . '
         ';

        return $sql;
    }
}

<?php

namespace App\Pagerfanta;

use Doctrine\DBAL\DBALException;
use Pagerfanta\Adapter\AdapterInterface;
use PDO;

class MessagesAdapter extends AbstractConversationsAdapter implements AdapterInterface
{
    protected function getConversationsQuery(): string
    {
        return '
            SELECT `m`.*
            FROM `messages` m
            WHERE
            ' . $this->getInitiatorCondition() . '
            AND ' . $this->getNotSpamCondition() . '
            AND ' . $this->getUnreadCondition() . '
            AND `m`.`request_id` IS NULL
            AND `m`.`id` IN (
                    SELECT max(`m`.`id`)
                    FROM `messages` m
                    WHERE ' . $this->getNotDeletedOrPurgedCondition() . '
                    GROUP BY `m`.`subject_id`
                )
            UNION
            SELECT `m`.*
            FROM `messages` m
            WHERE
            ' . $this->getInitiatorCondition() . '
            AND ' . $this->getNotSpamCondition() . '
            AND `m`.`request_id` IS NULL
            AND ' . $this->getUnreadCondition() . '
            AND `m`.`subject_id` IS NULL
            AND ' . $this->getNotDeletedOrPurgedCondition() . '
        ';
    }
}

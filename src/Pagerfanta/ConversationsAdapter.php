<?php

namespace App\Pagerfanta;

use Pagerfanta\Adapter\AdapterInterface;

class ConversationsAdapter extends AbstractConversationsAdapter implements AdapterInterface
{
    protected function getConversationsQuery(): string
    {
        return '
            SELECT `m`.*
            FROM `messages` m
            WHERE ' . $this->getInitiatorCondition() . '
            AND ' . $this->getUnreadCondition() . '
            AND `m`.`id` IN (
                    SELECT max(`m`.`id`)
                    FROM `messages` m
                    WHERE ' . $this->getNotDeletedOrPurgedCondition() . '
                    AND ' . $this->getNotSpamCondition() . '
                    GROUP BY `m`.`subject_id`
                )
            UNION
                SELECT `m`.*
                FROM `messages` m
                WHERE ' . $this->getInitiatorCondition() . '
                AND ' . $this->getUnreadCondition() . '
                AND ' . $this->getNotSpamCondition() . '
                AND `m`.`subject_id` IS NULL
                AND ' . $this->getNotDeletedOrPurgedCondition() . '
     ';
    }
}

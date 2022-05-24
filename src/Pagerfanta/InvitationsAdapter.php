<?php

namespace App\Pagerfanta;

use Pagerfanta\Adapter\AdapterInterface;

class InvitationsAdapter extends AbstractConversationsAdapter implements AdapterInterface
{
    protected function getConversationsQuery(): string
    {
        return '
            SELECT `m`.*
            FROM `messages` m
            LEFT JOIN `request` r ON `m`.`request_id` = `r`.`id`
            WHERE '
            . $this->getInitiatorCondition() . '
            AND ' . $this->getNotSpamCondition() . '
            AND ' . $this->getUnreadCondition() . '
            AND `r`.`invite_for_leg` IS NOT NULL
            AND `m`.`id` IN (
                    SELECT max(`m`.`id`)
                    FROM `messages` m
                    WHERE ' . $this->getNotDeletedOrPurgedCondition() . '
                    AND ' . $this->getNotSpamCondition() . '
                    GROUP BY `m`.`subject_id`
                )
        ';
    }
}

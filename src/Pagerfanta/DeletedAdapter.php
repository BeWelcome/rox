<?php

namespace App\Pagerfanta;

use App\Doctrine\DeleteRequestType;
use Pagerfanta\Adapter\AdapterInterface;

class DeletedAdapter extends AbstractConversationsAdapter implements AdapterInterface
{
    protected function getConversationsQuery(): string
    {
        return '
            SELECT `m`.*
            FROM `message` m
            WHERE
                `m`.`id` IN (
                    SELECT max(`m`.`id`)
                    FROM `message` m
                    WHERE ' . $this->getDeletedCondition() . '
                    AND ' . $this->getNotSpamCondition() . '
                    GROUP BY `m`.`subject_id`
                )
            UNION
            SELECT `m`.*
            FROM `message` m
            WHERE
                ' . $this->getDeletedCondition() . '
                AND ' . $this->getNotSpamCondition() . '
                AND `m`.`subject_id` IS NULL
        ';
    }

    private function getDeletedCondition(): string
    {
        $deletedCondition =
            '(' .
                '(receiver_id = :memberId AND (m.delete_request LIKE \'%' . DeleteRequestType::RECEIVER_DELETED . '%\'' .
                ' AND m.delete_request NOT LIKE \'%' . DeleteRequestType::RECEIVER_PURGED . '%\'))' .
            ' OR (sender_id = :memberId AND (m.delete_request LIKE \'%' . DeleteRequestType::SENDER_DELETED . '%\'' .
                ' AND m.delete_request NOT LIKE \'%' . DeleteRequestType::SENDER_PURGED . '%\'))' .
            ')';

        return $deletedCondition;
    }
}

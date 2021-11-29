<?php

namespace App\Pagerfanta;

use App\Doctrine\DeleteRequestType;
use Doctrine\DBAL\DBALException;
use Pagerfanta\Adapter\AdapterInterface;
use PDO;

class DeletedAdapter extends AbstractConversationsAdapter implements AdapterInterface
{
    protected function getConversationsQuery(): string
    {
        return '
            SELECT `m`.*
            FROM `messages` m
            WHERE
                `m`.`id` IN (
                    SELECT max(`m`.`id`)
                    FROM `messages` m
                    WHERE ' . $this->getDeletedCondition() . '
                    AND ' . $this->getNotSpamCondition() . '
                    GROUP BY `m`.`subject_id`
                )
            UNION
            SELECT `m`.*
            FROM `messages` m
            WHERE
                ' . $this->getDeletedCondition() . '
                AND ' . $this->getNotSpamCondition() . '
                AND `m`.`subject_id` IS NULL
        ';
    }

    private function getDeletedCondition(): string
    {
        $deletedCondition =
            '((IdReceiver = :memberId AND (m.DeleteRequest LIKE \'%' . DeleteRequestType::RECEIVER_DELETED . '%\'))
                OR (IdSender = :memberId AND (m.DeleteRequest LIKE \'%' . DeleteRequestType::SENDER_DELETED . '%\')))';

        return $deletedCondition;
    }
}

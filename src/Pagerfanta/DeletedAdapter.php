<?php

namespace App\Pagerfanta;

use App\Doctrine\DeleteRequestType;
use Pagerfanta\Adapter\AdapterInterface;

class DeletedAdapter extends AbstractConversationsAdapter implements AdapterInterface
{
    protected function getSqlQueryTemplate(): string
    {
        $sql = '
            SELECT %select%
            FROM `messages` m
            WHERE
                (IdReceiver = :memberId AND (m.DeleteRequest LIKE \'%' . DeleteRequestType::RECEIVER_DELETED . '%\'))
                OR (IdSender = :memberId AND (m.DeleteRequest LIKE \'%' . DeleteRequestType::SENDER_DELETED . '%\'))
        '
        ;

        return $sql;
    }
}

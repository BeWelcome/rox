<?php

namespace App\Pagerfanta;

use App\Doctrine\InFolderType;
use Pagerfanta\Adapter\AdapterInterface;

class SpamAdapter extends AbstractConversationsAdapter implements AdapterInterface
{
    protected function getConversationsQuery(): string
    {
        $sql = '
            SELECT `m`.*
            FROM `messages` m
            WHERE '
            . $this->getInitiatorCondition() . '
            AND ' . $this->getUnreadCondition() . '
            AND (`m`.`IdReceiver` = :memberId AND `m`.`infolder` = \'' . InFolderType::SPAM . '\')
            AND ' . $this->getNotDeletedOrPurgedCondition() . '
         ';

        return $sql;
    }
}

<?php

namespace App\Pagerfanta;

use App\Doctrine\InFolderType;
use Pagerfanta\Adapter\AdapterInterface;

class SpamAdapter extends AbstractConversationsAdapter implements AdapterInterface
{
    protected function getSqlQueryTemplate(): string
    {
        $sql = '
            SELECT %select%
            FROM `messages` m
            WHERE '
            . $this->getInitiatorCondition() . '
            AND ' . $this->getUnreadCondition() . '
            AND `m`.`infolder` = \'' . InFolderType::SPAM . '\'
            AND ' . $this->getNotDeletedOrPurgedCondition() . '
         ';

        return $sql;
    }
}

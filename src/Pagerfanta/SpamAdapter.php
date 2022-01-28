<?php

namespace App\Pagerfanta;

use App\Doctrine\InFolderType;
use App\Doctrine\SpamInfoType;
use Pagerfanta\Adapter\AdapterInterface;

class SpamAdapter extends AbstractConversationsAdapter implements AdapterInterface
{
    protected function getConversationsQuery(): string
    {
        $sql = '
            SELECT `m`.*
            FROM `messages` m
            WHERE
            `m`.`IdReceiver` = :memberId
            AND NOT `m`.`subject_id` IS NULL
            AND ' . $this->getUnreadCondition() . '
            AND (`m`.`IdReceiver` = :memberId AND `m`.`infolder` = \'' . InFolderType::SPAM . '\')
            AND ' . $this->getNotDeletedOrPurgedCondition() . '
            AND `m`.`id` in (
                    SELECT max(`m`.`id`)
                    FROM `messages` m
                    WHERE
                        `m`.`IdReceiver` = :memberId
                        AND ' . $this->getNotDeletedOrPurgedCondition() . '
                        AND ' . $this->getSpamCondition() . '
                    GROUP BY `m`.`subject_id`
            )
            UNION
            SELECT `m`.*
            FROM `messages` m
            WHERE
            `m`.`IdReceiver` = :memberId
            AND subject_id IS NULL
            AND ' . $this->getSpamCondition() . '
         ';

        return $sql;
    }

    private function getSpamCondition(): string
    {
        return 'SpamInfo LIKE \'%' . SpamInfoType::MEMBER_SAYS_SPAM . '%\' AND InFolder = \'' . InFolderType::SPAM . '\'';
    }

}

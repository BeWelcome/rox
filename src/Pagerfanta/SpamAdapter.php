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
            FROM `message` m
            WHERE
            `m`.`receiver_id` = :memberId
            AND NOT `m`.`subject_id` IS NULL
            AND ' . $this->getUnreadCondition() . '
            AND (`m`.`receiver_id` = :memberId AND `m`.`infolder` = \'' . InFolderType::SPAM . '\')
            AND ' . $this->getNotDeletedOrPurgedCondition() . '
            AND `m`.`id` in (
                    SELECT max(`m`.`id`)
                    FROM `message` m
                    WHERE
                        `m`.`receiver_id` = :memberId
                        AND ' . $this->getNotDeletedOrPurgedCondition() . '
                        AND ' . $this->getSpamCondition() . '
                    GROUP BY `m`.`subject_id`
            )
            UNION
            SELECT `m`.*
            FROM `message` m
            WHERE
            `m`.`receiver_id` = :memberId
            AND subject_id IS NULL
            AND ' . $this->getSpamCondition() . '
         ';

        return $sql;
    }

    private function getSpamCondition(): string
    {
        return '(spam_info LIKE \'%' . SpamInfoType::MEMBER_SAYS_SPAM . '%\' OR spam_info LIKE \'%' . SpamInfoType::SPAM_BLOCKED_WORD . '%\') AND folder = \'' . InFolderType::SPAM . '\'';
    }
}

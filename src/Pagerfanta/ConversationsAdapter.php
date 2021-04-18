<?php

namespace App\Pagerfanta;

use Doctrine\DBAL\DBALException;
use Pagerfanta\Adapter\AdapterInterface;
use PDO;

class ConversationsAdapter extends AbstractConversationsAdapter implements AdapterInterface
{
    /**
     * Returns the number of results.
     */
    public function getNbResults(): int
    {
        $count = 0;
        try {
            $sql = '
            SELECT count(`m`.`id`) AS count FROM (
                    SELECT `m`.`id`
                    FROM `messages` m
                    WHERE '
                    . $this->getInitiatorCondition() . '
                    AND ' . $this->getUnreadCondition() . '
                    AND `m`.`id` IN (
                            SELECT max(`m`.`id`)
                            FROM `messages` m
                            WHERE ' . $this->getNotDeletedOrPurgedCondition() . '
                            GROUP BY `m`.`subject_id`
                        )
                UNION
                    SELECT `m`.`id`
                    FROM `messages` m
                    WHERE '
                    . $this->getInitiatorCondition() . '
                    AND ' . $this->getUnreadCondition() . '
                    AND `m`.`subject_id` IS NULL
                    AND ' . $this->getNotDeletedOrPurgedCondition() . '
             ) m
         ';
            $stmt = $this->connection->executeQuery($sql, [':memberId' => $this->member->getId()], [PDO::PARAM_INT]);
            $row = $stmt->fetchAll(PDO::FETCH_OBJ);
            $count = ($row[0])->count;
        } catch (DBALException $e) {
            // Return 0
        }

        return $count;
    }

    protected function getSqlQueryTemplate(): string
    {
        $sql = '
            SELECT * FROM (
                    SELECT %select%
                    FROM `messages` m
                    WHERE '
                    . $this->getInitiatorCondition() . '
                    AND ' . $this->getUnreadCondition() . '
                    AND `m`.`id` IN (
                            SELECT max(`m`.`id`)
                            FROM `messages` m
                            WHERE ' . $this->getNotDeletedOrPurgedCondition() . '
                            GROUP BY `m`.`subject_id`
                        )
                UNION
                    SELECT %select%
                    FROM `messages` m
                    WHERE '
                    . $this->getInitiatorCondition() . '
                    AND ' . $this->getUnreadCondition() . '
                    AND `m`.`subject_id` IS NULL
                    AND ' . $this->getNotDeletedOrPurgedCondition() . '
             ) m
         ';

        return $sql;
    }
}

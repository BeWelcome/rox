<?php

namespace AppBundle\Model;

use AppBundle\Entity\Log;
use AppBundle\Repository\LogRepository;
use Doctrine\DBAL\DBALException;
use PDO;

class LogModel extends BaseModel
{
    /**
     * Returns a Pagerfanta object that contains the currently selected logs.
     *
     * @param array $types
     * @param $member
     * @param int $page
     * @param int $limit
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getFilteredLogs(array $types, $member, $page, $limit)
    {
        /** @var LogRepository $repository */
        $repository = $this->em->getRepository(Log::class);

        return $repository->findLatest($types, $member, $page, $limit);
    }

    public function getLogTypes()
    {
        $types = [];
        try {
            $connection = $this->em->getConnection();
            $stmt = $connection->prepare('
                SELECT 
                    `type`
                FROM
                  logs
                ORDER BY `type`
            ');
            $stmt->execute();
            $types = array_keys($stmt->fetchAll(PDO::FETCH_NUM | PDO::FETCH_UNIQUE));
            // Satisfy ChoiceType
            $types = array_combine($types, $types);
        } catch (DBALException $e) {
            // Return empty types array in case of DB problem.
        }

        return $types;
    }
}

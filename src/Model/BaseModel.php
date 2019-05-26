<?php

namespace App\Model;

use Doctrine\DBAL\Driver\Statement;

class BaseModel
{
    /**
     * @param $sql
     *
     * @throws \Exception
     *
     * @return Statement|\Doctrine\DBAL\Statement
     */
    public function execQuery($sql)
    {
        /* @var Statement $stm */
        try {
            $stm = $this->em->getConnection()->prepare($sql);
            if ($stm->execute()) {
                return $stm;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}

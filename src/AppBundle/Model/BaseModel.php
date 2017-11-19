<?php

namespace AppBundle\Model;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\ORM\EntityManager;

class BaseModel
{
    /** @var ManagerRegistry */
    protected $registry;

    /** @var EntityManager */
    protected $em;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
        $this->em = $registry->getManager();
    }

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

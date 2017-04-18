<?php

namespace AppBundle\Model;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\ORM\EntityManager;

class BaseModel
{
    /** @var Registry */
    protected $registry;

    /** @var EntityManager */
    protected $em;

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
        $this->em = $registry->getManager();
    }

    public function execQuery($sql)
    {
        /** @var Statement $stm */
        $stm = $this->em->getConnection()->prepare($sql);
        if ($stm->execute()) {
            return $stm;
        }
    }
}

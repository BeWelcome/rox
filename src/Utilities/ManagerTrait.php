<?php

namespace App\Utilities;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;

trait ManagerTrait
{
    /** @var ManagerRegistry */
    protected $registry;

    /** @var EntityManager */
    protected $em;

    /**
     * @required
     *
     * @param ManagerRegistry $registry
     */
    public function setManager(ManagerRegistry $registry)
    {
        $this->registry = $registry;
        $this->em = $registry->getManager();
    }

    public function getManager()
    {
        return $this->em;
    }
}

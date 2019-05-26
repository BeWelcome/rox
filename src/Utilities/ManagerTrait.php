<?php

namespace App\Utilities;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

trait ManagerTrait
{
    /** @var EntityManager */
    private $em;

    /**
     * @required
     * @param EntityManagerInterface $entityManager
     */
    public function setManager(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    public function getManager()
    {
        return $this->em;
    }
}

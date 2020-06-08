<?php

namespace App\Utilities;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

trait ManagerTrait
{
    /** @var EntityManager */
    private $em;

    /**
     * @required
     */
    public function setManager(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    protected function getManager()
    {
        return $this->em;
    }
}

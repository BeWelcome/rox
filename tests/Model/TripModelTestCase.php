<?php

namespace App\Tests\Model;

use App\Model\TripModel;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class TripModelTestCase extends TestCase
{
    protected function getTripModel(): TripModel
    {
        $entityManager = $this->createStub(EntityManagerInterface::class);

        return new TripModel($entityManager);
    }
}

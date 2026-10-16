<?php

namespace App\Tests\Model;

use App\Model\TripModel;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class TripModelTestCase extends TestCase
{
    protected function getTripModel(): TripModel
    {
        $entityManager = $this->createStub(EntityManagerInterface::class);
        $translator = $this->createStub(TranslatorInterface::class);

        return new TripModel($entityManager, $translator);
    }
}

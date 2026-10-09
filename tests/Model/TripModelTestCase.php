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
        // main's TripModel translates its error messages; return the key so tests can compare it.
        $translator->method('trans')->willReturnArgument(0);

        return new TripModel($entityManager, $translator);
    }
}

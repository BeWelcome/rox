<?php

namespace App\Processor\Fixtures;

use App\Entity\Location;
use Doctrine\ORM\EntityManagerInterface;
use Fidry\AliceDataFixtures\ProcessorInterface;
use LongitudeOne\Spatial\PHP\Types\Geometry\Point;

final readonly class LocationProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function preProcess(string $id, $object): void
    {
        if (!$object instanceof Location) {
            return;
        }

        $point = new Point($object->getLongitude(), $object->getLatitude(), 4326);
        $point->setX($object->getLongitude());
        $point->setY($object->getLatitude());
        $object->setCoordinates($point);
    }

    public function postProcess(string $id, $object): void
    {
        if (!$object instanceof Location) {
            return;
        }

        $this->entityManager->persist($object);
        $this->entityManager->flush();
    }
}

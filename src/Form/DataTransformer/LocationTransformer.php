<?php

namespace App\Form\DataTransformer;

use App\Entity\Location;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class LocationTransformer implements DataTransformerInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * Transforms a geoname id (string) into a Location entity.
     *
     * Direction: model (DTO: ?string geonameId) -> norm (Location)
     */
    public function transform($value): ?Location
    {
        if (null === $value || '' === $value) {
            return null;
        }

        /** @var Location|null $location */
        $location = $this->entityManager
            ->getRepository(Location::class)
            ->findOneBy(['geonameId' => $value]);

        if (null === $location) {
            throw new TransformationFailedException(\sprintf('A location with geonameId "%s" does not exist!', $value));
        }

        return $location;
    }

    /**
     * Transforms a Location entity into its geoname id (string).
     *
     * Direction: norm (Location) -> model (DTO: ?string geonameId)
     */
    public function reverseTransform($value): ?string
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof Location) {
            throw new TransformationFailedException('Expected an instance of App\\Entity\\Location.');
        }

        return $value->getGeonameId();
    }
}

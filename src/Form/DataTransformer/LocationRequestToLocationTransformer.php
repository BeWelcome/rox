<?php

namespace App\Form\DataTransformer;

use App\Entity\Location;
use App\Form\CustomDataClass\LocationRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class LocationRequestToLocationTransformer implements DataTransformerInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Transforms a location to a location request.
     *
     * @param mixed $value
     */
    public function transform($value): ?LocationRequest
    {
        if (null === $value) {
            return null;
        }

        $locationRequest = new LocationRequest($value);

        return $locationRequest;
    }

    /**
     * Transforms a location request to a location.
     *
     * @param LocationRequest $value
     *
     * @throws TransformationFailedException if location is not found
     */
    public function reverseTransform($value): ?Location
    {
        if (null === $value) {
            return null;
        }

        if (null === $value->geonameId) {
            $failure = new TransformationFailedException("location.none.given - test");
            $failure->setInvalidMessage("location.none.given - test");
            throw $failure;
        }

        /** @var Location $location */
        $location = $this->entityManager
            ->getRepository(Location::class)
            ->findOneBy(['geonameId' => $value->geonameId]);

        if (null === $location) {
            // causes a validation error
            // this message is not shown to the user
            // see the invalid_message option
            $message = sprintf(
                'A location with geonameId "%d" for %s does not exist!',
                $value->geonameId,
                $value->name
            );
            throw new TransformationFailedException($message);
        }

        return $location;
    }
}

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
     * @param Location|null $location
     */
    public function transform($location): ?LocationRequest
    {
        if (null === $location) {
            return null;
        }

        $locationRequest = new LocationRequest($location);

        return $locationRequest;
    }

    /**
     * Transforms a location request to a location.
     *
     * @param LocationRequest $locationRequest
     *
     * @throws TransformationFailedException if location is not found
     */
    public function reverseTransform($locationRequest): ?Location
    {
        if (null === $locationRequest) {
            return null;
        }

        if (null === $locationRequest->geoname_id) {
            return null;
        }

        /** @var Location $location */
        $location = $this->entityManager
            ->getRepository(Location::class)
            // query for the issue with this id
            ->findOneBy(['geonameId' => $locationRequest->geoname_id]);

        if (null === $location) {
            // causes a validation error
            // this message is not shown to the user
            // see the invalid_message option
            $message = sprintf(
                'A location with geonameId "%d" for %s does not exist!',
                $locationRequest->geoname_id,
                $locationRequest->name
            );
            throw new TransformationFailedException($message);
        }

        return $location;
    }
}

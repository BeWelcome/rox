<?php

namespace App\Form\DataTransformer;

use App\Entity\Location;
use App\Entity\Subtrip;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class LegTransformer implements DataTransformerInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function transform($value): ?string
    {
        if (null === $value) {
            return null;
        }

        return $value->getId();
    }

    public function reverseTransform($value): ?Subtrip
    {
        if (null === $value) {
            return null;
        }

        /** @var Subtrip $leg */
        $leg = $this->entityManager
            ->getRepository(Subtrip::class)
            ->findOneBy(['id' => $value]);

        if (null === $leg) {
            // causes a validation error
            // this message is not shown to the user
            // see the invalid_message option
            $message = sprintf(
                'A leg with id "%d" does not exist!',
                $value
            );
            throw new TransformationFailedException($message);
        }

        return $leg;
    }
}

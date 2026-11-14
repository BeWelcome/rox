<?php

namespace App\Form\DataTransformer;

use App\Entity\Leg;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class LegTransformer implements DataTransformerInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function transform($value): ?string
    {
        if (null === $value) {
            return null;
        }

        return $value->getId();
    }

    public function reverseTransform($value): ?Leg
    {
        if (null === $value) {
            return null;
        }

        /** @var Leg $leg */
        $leg = $this->entityManager
            ->getRepository(Leg::class)
            ->findOneBy(['id' => $value]);

        if (null === $leg) {
            // causes a validation error
            // this message is not shown to the user
            // see the invalid_message option
            $message = \sprintf(
                'A leg with id "%d" does not exist!',
                $value
            );
            throw new TransformationFailedException($message);
        }

        return $leg;
    }
}
